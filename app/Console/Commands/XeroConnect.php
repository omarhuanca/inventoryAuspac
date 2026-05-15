<?php

namespace App\Console\Commands;

use App\Modules\Xero\Service\XeroAuthService;
use Illuminate\Console\Command;

class XeroConnect extends Command
{
    protected $signature   = 'xero:connect';
    protected $description = 'Interactively authorise this app with Xero via OAuth2';

    public function __construct(private XeroAuthService $xeroAuthService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('  Xero OAuth2 – Interactive Connect');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Check current status
        $status = $this->xeroAuthService->getConnectionStatus();
        if ($status['connected'] && !$status['expired']) {
            $this->info('Already connected!');
            $this->table(['Field', 'Value'], [
                ['Tenant ID',   $status['tenant_id']],
                ['Expires at',  $status['expires_at']],
                ['Expired',     $status['expired'] ? 'Yes' : 'No'],
            ]);

            if (!$this->confirm('Re-authorise anyway?', false)) {
                return self::SUCCESS;
            }
        }

        // Step 1 – generate the authorization URL
        $provider = $this->xeroAuthService->buildProvider();
        ['url' => $authUrl, 'state' => $state] = $this->xeroAuthService->getAuthorizationUrl($provider);

        $this->newLine();
        $this->line('<fg=yellow>STEP 1 – Open this URL in your browser:</>');
        $this->newLine();
        $this->line("  <fg=cyan>{$authUrl}</>");
        $this->newLine();
        $this->line('Log in to Xero, select your organisation, and click Allow access.');
        $this->line('Xero will redirect you to a URL that looks like:');
        $this->line('  <fg=gray>http://localhost/api/xero/callback?code=XXX&state=YYY</>');
        $this->newLine();

        // Step 2 – user pastes the full callback URL (or just the code)
        $input = $this->ask('STEP 2 – Paste the full callback URL (or just the "code" value)');

        if (empty($input)) {
            $this->error('No input received. Aborting.');
            return self::FAILURE;
        }

        // Extract code and state from full URL or bare code
        $code         = null;
        $returnedState = null;

        if (str_starts_with($input, 'http')) {
            $parts        = parse_url($input);
            parse_str($parts['query'] ?? '', $params);
            $code          = $params['code']  ?? null;
            $returnedState = $params['state'] ?? null;
        } else {
            // Assume bare code was pasted
            $code = trim($input);
        }

        if (empty($code)) {
            $this->error('Could not extract "code" from the input. Aborting.');
            return self::FAILURE;
        }

        // Validate CSRF state when available
        if ($returnedState !== null && $returnedState !== $state) {
            $this->error('OAuth state mismatch – possible CSRF. Aborting.');
            return self::FAILURE;
        }

        // Step 3 – exchange code for token
        $this->newLine();
        $this->line('Exchanging authorisation code for tokens…');

        try {
            $tenantId = $this->xeroAuthService->handleCallback($code);
        } catch (\Exception $e) {
            $this->error('Token exchange failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        // Step 4 – verify with a real API call
        $this->line('Verifying connection with Xero API…');

        try {
            $api       = $this->xeroAuthService->getAccountingApi();
            $orgs      = $api->getOrganisations($tenantId);
            $orgName   = $orgs->getOrganisations()[0]->getName() ?? '(unknown)';
            $orgCountry = $orgs->getOrganisations()[0]->getCountryCode() ?? '–';
        } catch (\Exception $e) {
            $this->error('Could not verify connection: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('✓ Connected to Xero successfully!');
        $this->table(['Field', 'Value'], [
            ['Organisation', $orgName],
            ['Country',      $orgCountry],
            ['Tenant ID',    $tenantId],
        ]);

        return self::SUCCESS;
    }
}
