<?php

namespace App\Modules\TaxCore\Service;

use GuzzleHttp\Client;

class TaxCoreCertificateService
{
    private ?array $cachedCerts = null;

    /**
     * Read the PFX and return the extracted certificate and private key as PEM strings.
     * Result: ['cert' => string, 'pkey' => string]
     *
     * Strategy:
     *   1. PHP's openssl_pkcs12_read (fast, no extra process)
     *   2. External openssl.exe fallback (handles legacy RC2/3DES PFX that OpenSSL 3.x rejects)
     */
    public function loadCertificate(): array
    {
        if ($this->cachedCerts !== null) return $this->cachedCerts;
        
        $pfxPath     = config('taxcore.pfx_path');
        $pfxPassword = (string) config('taxcore.pfx_password', '');

        if (!$pfxPath || !file_exists($pfxPath)) {
            throw new \RuntimeException("TaxCore PFX certificate not found at: [{$pfxPath}]. Set TAXCORE_PFX_PATH in .env");
        }

        $pfxContent = file_get_contents($pfxPath);
        $certs = [];

        // Attempt 1: PHP built-in (works when OpenSSL 3.x legacy provider is enabled)
        if (@openssl_pkcs12_read($pfxContent, $certs, $pfxPassword)) {
            $this->cachedCerts = $certs;
            return $certs;
        }

        // Attempt 2: External openssl binary (Git for Windows includes OpenSSL 1.1.x which
        // supports legacy RC2/3DES PFX files that OpenSSL 3.x rejects by default)
        $certs = $this->loadViaExternalOpenssl($pfxPath, $pfxPassword);

        if (!empty($certs)) {
            $this->cachedCerts = $certs;
            return $certs;
        }

        // Both methods failed — collect the OpenSSL error for a useful message
        $errors = [];
        while ($err = openssl_error_string()) {
            $errors[] = $err;
        }

        throw new \RuntimeException(
            'Failed to read TaxCore PFX certificate. ' .
            'Verify TAXCORE_PFX_PASSWORD is correct. ' .
            'OpenSSL errors: ' . (implode(' | ', $errors) ?: 'none') . '. ' .
            'If the PFX uses legacy encryption (RC2/3DES), set TAXCORE_OPENSSL_BIN in .env ' .
            'to the path of openssl.exe (e.g. C:/Program Files/Git/mingw64/bin/openssl.exe).'
        );
    }

    /**
     * Write cert and key as .pem files to storage/app/taxcore/ so Guzzle can use them.
     * Returns ['cert' => path, 'key' => path].
     */
    public function writePemFiles(): array
    {
        $certs = $this->loadCertificate();

        $dir = storage_path('app/taxcore');

        if (!is_dir($dir) && !mkdir($dir, 0700, true)) {
            throw new \RuntimeException("Cannot create TaxCore cert directory: {$dir}");
        }

        $certPath = $dir . DIRECTORY_SEPARATOR . 'client.pem';
        $keyPath  = $dir . DIRECTORY_SEPARATOR . 'client.key';

        if (file_put_contents($certPath, $certs['cert']) === false) {
            throw new \RuntimeException("Cannot write TaxCore client cert to: {$certPath}");
        }

        if (file_put_contents($keyPath, $certs['pkey']) === false) {
            throw new \RuntimeException("Cannot write TaxCore client key to: {$keyPath}");
        }

        chmod($certPath, 0600);
        chmod($keyPath, 0600);

        return ['cert' => $certPath, 'key' => $keyPath];
    }

    /**
     * Derive the TaxCore API base URL.
     *
     * Priority:
     *   1. TAXCORE_ENDPOINT_OVERRIDE (.env / config)
     *   2. URI in the certificate's Subject Alternative Name extension
     *
     * @throws \RuntimeException when the URL cannot be determined.
     */
    public function getEndpoint(): string
    {
        $override = config('taxcore.endpoint_override');

        if ($override) return rtrim($override, '/');
        
        $certs    = $this->loadCertificate();
        $certInfo = openssl_x509_parse($certs['cert']);

        if ($certInfo === false) {
            throw new \RuntimeException('Failed to parse TaxCore X.509 certificate.');
        }

        // 1. Check Subject Alternative Name for URI entries only
        $san = $certInfo['extensions']['subjectAltName'] ?? '';
        preg_match_all('/URI:([^\s,]+)/i', $san, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $uri) {
                // Skip PKI infrastructure URLs (CRL, OCSP, PKI distribution points)
                if (!preg_match('#/(crl|ocsp|pki|ca|certs?)(/|$)#i', $uri)) {
                    return rtrim($uri, '/');
                }
            }
        }

        throw new \RuntimeException(
            'The TaxCore API endpoint is not embedded in the certificate SAN. ' .
            'Set TAXCORE_ENDPOINT_OVERRIDE in .env. ' .
            'You can find the endpoint in the TAP Developer Portal under Resources, ' .
            'or in the VSDC Request Submitter tool at https://tap.sandbox.taxcore.online/'
        );
    }

    /**
     * Build a Guzzle client pre-configured for mTLS using the loaded certificate.
     */
    public function buildGuzzleClient(): Client
    {
        $pem = $this->writePemFiles();

        return new Client([
            'cert' => $pem['cert'],
            'ssl_key' => $pem['key'],
            'verify' => config('taxcore.verify_ssl', true),
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Return parsed details from the certificate (subject, issuer, validity).
     * Useful for debugging / status endpoints.
     */
    public function getCertificateInfo(): array
    {
        $certs    = $this->loadCertificate();
        $certInfo = openssl_x509_parse($certs['cert']);

        if ($certInfo === false) {
            return [];
        }

        return [
            'subject' => $certInfo['subject']    ?? [],
            'issuer' => $certInfo['issuer']     ?? [],
            'valid_from' => date('Y-m-d H:i:s', $certInfo['validFrom_time_t'] ?? 0),
            'valid_to' => date('Y-m-d H:i:s', $certInfo['validTo_time_t']   ?? 0),
            'serial' => $certInfo['serialNumberHex'] ?? '',
            'san' => $certInfo['extensions']['subjectAltName'] ?? '',
        ];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Use an external openssl binary to extract cert and private key from the PFX.
     * This handles legacy-encrypted PFX files (RC2/3DES) that PHP's OpenSSL 3.x rejects.
     *
     * @return array ['cert' => pem_string, 'pkey' => pem_string] or [] on failure.
     */
    private function loadViaExternalOpenssl(string $pfxPath, string $password): array
    {
        $opensslBin = $this->findExternalOpenssl();

        if ($opensslBin === null) return [];
    
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'taxcore_' . bin2hex(random_bytes(4));

        if (!mkdir($tempDir, 0700)) return [];

        $certPath = $tempDir . DIRECTORY_SEPARATOR . 'cert.pem';
        $keyPath  = $tempDir . DIRECTORY_SEPARATOR . 'key.pem';
        $passFile = $tempDir . DIRECTORY_SEPARATOR . 'pass.tmp';

        try {
            // Write the password to a temp file so it never appears in a process listing
            file_put_contents($passFile, $password);
            chmod($passFile, 0600);

            $passinArg = 'file:' . $passFile;

            // Extract certificate — try -legacy flag (OpenSSL 3.x) first, then without (1.1.x)
            $certOk = $this->runOpenssl($opensslBin, [
                'pkcs12', '-legacy',
                '-in', $pfxPath, '-passin', $passinArg,
                '-nokeys', '-clcerts', '-out', $certPath,
            ]) || $this->runOpenssl($opensslBin, [
                'pkcs12',
                '-in', $pfxPath, '-passin', $passinArg,
                '-nokeys', '-clcerts', '-out', $certPath,
            ]);

            if (!$certOk || !file_exists($certPath)) {
                return [];
            }

            // Extract private key (unencrypted)
            $keyOk = $this->runOpenssl($opensslBin, [
                'pkcs12', '-legacy',
                '-in', $pfxPath, '-passin', $passinArg,
                '-nocerts', '-nodes', '-out', $keyPath,
            ]) || $this->runOpenssl($opensslBin, [
                'pkcs12',
                '-in', $pfxPath, '-passin', $passinArg,
                '-nocerts', '-nodes', '-out', $keyPath,
            ]);

            if (!$keyOk || !file_exists($keyPath)) return [];

            return [
                'cert' => file_get_contents($certPath),
                'pkey' => file_get_contents($keyPath),
            ];
        } finally {
            // Clean up every temp file regardless of outcome
            foreach ([$certPath, $keyPath, $passFile] as $f) {
                if (file_exists($f)) {
                    @unlink($f);
                }
            }
            @rmdir($tempDir);
        }
    }

    /**
     * Find a usable openssl binary.
     * Checks TAXCORE_OPENSSL_BIN config first, then common Windows (Git for Windows) paths.
     */
    private function findExternalOpenssl(): ?string
    {
        $configured = config('taxcore.openssl_bin');
        if ($configured && file_exists($configured)) return $configured;
        

        $candidates = [
            'C:\\Program Files\\Git\\mingw64\\bin\\openssl.exe',
            'C:\\Program Files\\Git\\usr\\bin\\openssl.exe',
            'C:\\Program Files (x86)\\Git\\mingw64\\bin\\openssl.exe',
            '/usr/bin/openssl',
            '/usr/local/bin/openssl',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Run an openssl command via proc_open (array form — no shell, no injection risk).
     * Returns true when the process exits with code 0.
     */
    private function runOpenssl(string $bin, array $args): bool
    {
        $cmd = array_merge([$bin], $args);

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptors, $pipes);

        if (!is_resource($process)) return false;

        fclose($pipes[0]);
        stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return proc_close($process) === 0;
    }
}
