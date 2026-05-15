<?php

namespace App\Modules\Xero\Service;

use App\Modules\Xero\Repository\XeroTokenRepository;
use Carbon\Carbon;
use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\GenericProvider;
use XeroAPI\XeroPHP\Api\AccountingApi;
use XeroAPI\XeroPHP\Api\IdentityApi;
use XeroAPI\XeroPHP\Configuration;

class XeroAuthService
{
    private XeroTokenRepository $storage;

    public function __construct(XeroTokenRepository $storage)
    {
        $this->storage = $storage;
    }

    private function httpClient(): Client
    {
        return new Client(['verify' => storage_path('cacert.pem')]);
    }

    public function buildProvider(): GenericProvider
    {
        return new GenericProvider(
            [
                'clientId' => config('xero.client_id'),
                'clientSecret' => config('xero.client_secret'),
                'redirectUri' => config('xero.redirect_uri'),
                'urlAuthorize' => config('xero.url_authorize'),
                'urlAccessToken' => config('xero.url_access_token'),
                'urlResourceOwnerDetails' => config('xero.url_resource_owner'),
            ],
            ['httpClient' => $this->httpClient()]
        );
    }

    public function getAuthorizationUrl(GenericProvider $provider): array
    {
        $url   = $provider->getAuthorizationUrl(['scope' => [config('xero.scopes')]]);
        $state = $provider->getState();

        return ['url' => $url, 'state' => $state];
    }


    public function handleCallback(string $code): string
    {
        $provider = $this->buildProvider();
        $accessToken = $provider->getAccessToken('authorization_code', ['code' => $code]);

        $config = Configuration::getDefaultConfiguration()->setAccessToken($accessToken->getToken());
        $identityApi = new IdentityApi($this->httpClient(), $config);
        $connections = $identityApi->getConnections();

        $this->storage->store(
            $accessToken->getToken(),
            $accessToken->getExpires(),
            $connections[0]->getTenantId(),
            $accessToken->getRefreshToken(),
            $accessToken->getValues()['id_token'] ?? ''
        );

        return $connections[0]->getTenantId();
    }

    public function refreshIfExpired(): void
    {
        $token = $this->storage->getLatest();

        if ($token === null) {
            throw new \RuntimeException('No Xero token found. Please authorize first via GET /api/xero/auth.');
        }

        // Refresh proactively 5 minutes before expiry to avoid race conditions with the 30-min window
        if (Carbon::now()->lessThan($token->expires_at->subMinutes(5))) {
            return;
        }

        $provider = $this->buildProvider();
        $newToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $token->refresh_token,
        ]);

        $this->storage->store(
            $newToken->getToken(),
            $newToken->getExpires(),
            $token->tenant_id,
            $newToken->getRefreshToken(),
            $newToken->getValues()['id_token'] ?? $token->id_token
        );
    }


    public function getAccountingApi(): AccountingApi
    {
        $this->refreshIfExpired();
        $config = Configuration::getDefaultConfiguration()
            ->setAccessToken($this->storage->getLatest()->access_token);

        return new AccountingApi($this->httpClient(), $config);
    }

    /**
     * Force a token refresh regardless of expiry. Used as a fallback when Xero returns 401.
     */
    public function forceRefresh(): void
    {
        $token = $this->storage->getLatest();

        if ($token === null) {
            throw new \RuntimeException('No Xero token found. Please authorize first via GET /api/xero/auth.');
        }

        $provider = $this->buildProvider();
        $newToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $token->refresh_token,
        ]);

        $this->storage->store(
            $newToken->getToken(),
            $newToken->getExpires(),
            $token->tenant_id,
            $newToken->getRefreshToken(),
            $newToken->getValues()['id_token'] ?? $token->id_token
        );
    }

    public function getTenantId(): string
    {
        $token = $this->storage->getLatest();
        if ($token === null) throw new \RuntimeException('No Xero token found. Please authorize first via GET /api/xero/auth.');
        
        return $token->tenant_id;
    }

    public function getConnectionStatus(): array
    {
        $token = $this->storage->getLatest();

        if ($token === null) return ['connected' => false];
        
        return [
            'connected' => true,
            'tenant_id' => $token->tenant_id,
            'expires_at' => $token->expires_at->toIso8601String(),
            'expired' => $token->hasExpired(),
        ];
    }
}
