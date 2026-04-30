<?php

namespace Tests\Unit;

use App\Modules\Xero\Domain\XeroToken;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class XeroTokenTest extends TestCase
{
    private function makeToken(Carbon $expiresAt): XeroToken
    {
        $token = new XeroToken();
        $token->setRawAttributes(['expires_at' => $expiresAt->format('Y-m-d H:i:s')]);
        return $token;
    }

    public function test_has_expired_returns_true_when_token_is_expired()
    {
        $token = $this->makeToken(Carbon::now()->subMinutes(5));

        $this->assertTrue($token->hasExpired());
    }

    public function test_has_expired_returns_false_when_token_is_not_expired()
    {
        $token = $this->makeToken(Carbon::now()->addMinutes(30));

        $this->assertFalse($token->hasExpired());
    }

    public function test_has_expired_returns_true_when_token_expires_exactly_now()
    {
        $token = $this->makeToken(Carbon::now());

        $this->assertTrue($token->hasExpired());
    }
}
