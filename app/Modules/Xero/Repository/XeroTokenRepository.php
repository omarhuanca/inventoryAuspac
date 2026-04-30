<?php

namespace App\Modules\Xero\Repository;

use App\Modules\Xero\Domain\XeroToken;
use Carbon\Carbon;

class XeroTokenRepository
{
    public function store(
        string $accessToken,
        int    $expires,
        string $tenantId,
        string $refreshToken,
        string $idToken
    ): XeroToken {
        return XeroToken::updateOrCreate(
            ['tenant_id' => $tenantId],
            [
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'id_token'      => $idToken,
                'expires_at'    => Carbon::createFromTimestamp($expires),
            ]
        );
    }

    public function getLatest(): ?XeroToken
    {
        return XeroToken::latest('id')->first();
    }

    public function hasExpired(): bool
    {
        $token = $this->getLatest();

        return $token === null || $token->hasExpired();
    }
}
