<?php

namespace App\Modules\TaxCore\Service;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;

class TaxCoreAuthService
{
    private const CACHE_KEY = 'taxcore_token';

    public function __construct(
        private TaxCoreCertificateService $certService
    ) {}

    /**
     * Return a valid bearer token, fetching a new one if the cache is empty or expired.
     */
    public function getToken(): string
    {
        $cached = Cache::get(self::CACHE_KEY);

        if ($cached) return $cached;
        
        return $this->fetchToken();
    }

    /**
     * Force a new token request regardless of cache state.
     * Called on 401 responses from downstream services.
     */
    public function forceRefresh(): string
    {
        Cache::forget(self::CACHE_KEY);

        return $this->fetchToken();
    }

    /**
     * Request a token from the V-SDC authentication endpoint.
     * TaxCore uses mTLS + PIN. The PIN is sent in the request body alongside the
     * certificate-based mutual TLS authentication.
     *
     * The response structure varies by jurisdiction; we try common field names in order.
     */
    private function fetchToken(): string
    {
        $client   = $this->certService->buildGuzzleClient();
        $baseUrl  = $this->certService->getEndpoint();
        $authPath = config('taxcore.paths.auth', '/vsdcconfig');

        $pin = (string) config('taxcore.pin', '');

        // The E-SDC PIN endpoint expects the 4-digit PIN as a raw plain-text body
        // (not wrapped in a JSON object). Content-Type: application/json is still required.
        // Successful response: "0100"  |  Wrong PIN: "2100"  |  Locked: "2110"
        try {
            $response = $client->post($baseUrl . $authPath, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => $pin,
            ]);
        } catch (ClientException $e) {
            $errBody = $e->getResponse()->getBody()->getContents();
            throw new \RuntimeException("TaxCore PIN verification failed [{$e->getCode()}]: {$errBody}", $e->getCode(), $e);
        }

        $result = trim($response->getBody()->getContents(), '" ');

        if ($result !== '0100') {
            throw new \RuntimeException("TaxCore PIN verification failed. E-SDC responded: \"{$result}\". " . 'Expected "0100". Check PIN value or E-SDC card status.');
        }

        // Cache the verified state. The TTL is short because E-SDC forgets the PIN
        // if restarted or the card is removed. A failed invoice call (1500 warning)
        // will trigger forceRefresh() to re-send the PIN.
        $ttl = (int) config('taxcore.token_ttl', 3600);
        Cache::put(self::CACHE_KEY, $result, now()->addSeconds($ttl));

        return $result;
    }
}
