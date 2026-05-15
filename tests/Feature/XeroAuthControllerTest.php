<?php

namespace Tests\Feature;

use App\Modules\Xero\Domain\XeroToken;
use App\Modules\Xero\Service\XeroAuthService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class XeroAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── GET /api/xero/status ─────────────────────────────────────────────────

    public function test_status_returns_not_connected_when_no_token_exists()
    {
        $response = $this->getJson('/api/xero/status');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'data'  => ['connected' => false],
            ]);
    }

    public function test_status_returns_connected_when_valid_token_exists()
    {
        XeroToken::create([
            'access_token'  => 'access-abc',
            'refresh_token' => 'refresh-abc',
            'id_token'      => 'id-abc',
            'tenant_id'     => 'tenant-123',
            'expires_at'    => Carbon::now()->addMinutes(25),
        ]);

        $response = $this->getJson('/api/xero/status');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'data'  => [
                    'connected' => true,
                    'tenant_id' => 'tenant-123',
                    'expired'   => false,
                ],
            ]);
    }

    public function test_status_returns_expired_true_when_token_has_expired()
    {
        XeroToken::create([
            'access_token'  => 'access-old',
            'refresh_token' => 'refresh-old',
            'id_token'      => 'id-old',
            'tenant_id'     => 'tenant-123',
            'expires_at'    => Carbon::now()->subMinutes(5),
        ]);

        $response = $this->getJson('/api/xero/status');

        $response->assertStatus(200)
            ->assertJson([
                'error' => false,
                'data'  => [
                    'connected' => true,
                    'expired'   => true,
                ],
            ]);
    }

    public function test_status_response_includes_expires_at_field()
    {
        $expiresAt = Carbon::now()->addHour();

        XeroToken::create([
            'access_token'  => 'access-abc',
            'refresh_token' => 'refresh-abc',
            'id_token'      => 'id-abc',
            'tenant_id'     => 'tenant-123',
            'expires_at'    => $expiresAt,
        ]);

        $response = $this->getJson('/api/xero/status');

        $response->assertStatus(200)
            ->assertJsonPath('data.expires_at', $expiresAt->toIso8601String());
    }

    public function test_authorize_redirects_to_xero_login()
    {
        $response = $this->get('/api/xero/auth');

        $response->assertStatus(302);
        $this->assertStringContainsString(
            'login.xero.com',
            $response->headers->get('Location')
        );
    }

    public function test_authorize_redirect_url_contains_client_id()
    {
        $response = $this->get('/api/xero/auth');

        $this->assertStringContainsString(
            config('xero.client_id'),
            $response->headers->get('Location')
        );
    }

    public function test_authorize_redirect_url_contains_redirect_uri()
    {
        $response = $this->get('/api/xero/auth');

        $this->assertStringContainsString(
            urlencode(config('xero.redirect_uri')),
            $response->headers->get('Location')
        );
    }

    public function test_authorize_stores_oauth_state_in_cache()
    {
        $this->get('/api/xero/auth');

        $this->assertNotNull(Cache::get('xero_oauth2_state'));
    }

    public function test_callback_returns_error_when_no_code_is_provided()
    {
        $response = $this->getJson('/api/xero/callback');

        $response->assertStatus(400)
            ->assertJson([
                'error'   => true,
                'message' => 'No authorization code returned by Xero.',
            ]);
    }

    public function test_callback_returns_error_when_state_is_missing()
    {
        $response = $this->getJson('/api/xero/callback?code=some_code');

        $response->assertStatus(422)
            ->assertJson([
                'error'   => true,
                'message' => 'Invalid OAuth state. Possible CSRF attack.',
            ]);
    }

    public function test_callback_returns_error_when_state_does_not_match_cache()
    {
        Cache::put('xero_oauth2_state', 'correct_state', now()->addMinutes(10));

        $response = $this->getJson('/api/xero/callback?code=some_code&state=wrong_state');

        $response->assertStatus(422)
            ->assertJson([
                'error'   => true,
                'message' => 'Invalid OAuth state. Possible CSRF attack.',
            ]);
    }

    public function test_callback_stores_token_and_returns_tenant_id_on_success()
    {
        $this->mock(XeroAuthService::class, function ($mock) {
            $mock->shouldReceive('handleCallback')
                ->once()
                ->with('valid_code')
                ->andReturn('tenant-456');
        });

        Cache::put('xero_oauth2_state', 'valid_state', now()->addMinutes(10));

        $response = $this->getJson('/api/xero/callback?code=valid_code&state=valid_state');

        $response->assertStatus(200)
            ->assertJson([
                'error'   => false,
                'message' => 'Xero connected successfully.',
                'data'    => ['tenant_id' => 'tenant-456'],
            ]);
    }

    public function test_callback_clears_state_from_cache_after_success()
    {
        $this->mock(XeroAuthService::class, function ($mock) {
            $mock->shouldReceive('handleCallback')->andReturn('tenant-456');
        });

        Cache::put('xero_oauth2_state', 'valid_state', now()->addMinutes(10));

        $this->getJson('/api/xero/callback?code=valid_code&state=valid_state');

        $this->assertNull(Cache::get('xero_oauth2_state'));
    }

    public function test_callback_clears_state_from_cache_on_csrf_failure()
    {
        Cache::put('xero_oauth2_state', 'correct_state', now()->addMinutes(10));

        $this->getJson('/api/xero/callback?code=some_code&state=wrong_state');

        $this->assertNull(Cache::get('xero_oauth2_state'));
    }

    public function test_callback_returns_error_when_xero_throws_exception()
    {
        $this->mock(XeroAuthService::class, function ($mock) {
            $mock->shouldReceive('handleCallback')
                ->once()
                ->andThrow(new \Exception('Xero rejected the token.'));
        });

        Cache::put('xero_oauth2_state', 'valid_state', now()->addMinutes(10));

        $response = $this->getJson('/api/xero/callback?code=bad_code&state=valid_state');

        $response->assertStatus(500)
            ->assertJson([
                'error'   => true,
                'message' => 'Xero callback failed: Xero rejected the token.',
            ]);
    }
}
