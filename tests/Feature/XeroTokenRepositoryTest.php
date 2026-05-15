<?php

namespace Tests\Feature;

use App\Modules\Xero\Domain\XeroToken;
use App\Modules\Xero\Repository\XeroTokenRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XeroTokenRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private XeroTokenRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = app(XeroTokenRepository::class);
    }

    // ─── getLatest() ──────────────────────────────────────────────────────────

    public function test_get_latest_returns_null_when_no_tokens_exist()
    {
        $this->assertNull($this->repo->getLatest());
    }

    public function test_get_latest_returns_token_after_storing_one()
    {
        $this->repo->store('access-1', now()->addHour()->timestamp, 'tenant-1', 'refresh-1', 'id-1');

        $token = $this->repo->getLatest();

        $this->assertNotNull($token);
        $this->assertEquals('tenant-1', $token->tenant_id);
        $this->assertEquals('access-1', $token->access_token);
    }

    public function test_get_latest_returns_most_recently_created_token()
    {
        $this->repo->store('access-1', now()->addHour()->timestamp, 'tenant-A', 'refresh-1', 'id-1');
        $this->repo->store('access-2', now()->addHour()->timestamp, 'tenant-B', 'refresh-2', 'id-2');

        $token = $this->repo->getLatest();

        $this->assertEquals('tenant-B', $token->tenant_id);
    }

    // ─── store() ──────────────────────────────────────────────────────────────

    public function test_store_creates_new_record_in_database()
    {
        $this->repo->store('access-abc', now()->addHour()->timestamp, 'tenant-99', 'refresh-abc', 'id-abc');

        $this->assertDatabaseHas('xero_tokens', [
            'tenant_id'     => 'tenant-99',
            'access_token'  => 'access-abc',
            'refresh_token' => 'refresh-abc',
            'id_token'      => 'id-abc',
        ]);
    }

    public function test_store_returns_xero_token_instance()
    {
        $token = $this->repo->store('access-abc', now()->addHour()->timestamp, 'tenant-99', 'refresh-abc', 'id-abc');

        $this->assertInstanceOf(XeroToken::class, $token);
    }

    public function test_store_updates_existing_token_for_same_tenant()
    {
        $expires = now()->addHour()->timestamp;

        $this->repo->store('access-old', $expires, 'tenant-X', 'refresh-old', 'id-old');
        $this->repo->store('access-new', $expires, 'tenant-X', 'refresh-new', 'id-new');

        // Only one record should exist for this tenant
        $this->assertDatabaseCount('xero_tokens', 1);
        $this->assertDatabaseHas('xero_tokens', [
            'tenant_id'    => 'tenant-X',
            'access_token' => 'access-new',
        ]);
    }

    public function test_store_creates_separate_records_for_different_tenants()
    {
        $expires = now()->addHour()->timestamp;

        $this->repo->store('access-1', $expires, 'tenant-A', 'refresh-1', 'id-1');
        $this->repo->store('access-2', $expires, 'tenant-B', 'refresh-2', 'id-2');

        $this->assertDatabaseCount('xero_tokens', 2);
    }

    public function test_store_sets_expires_at_from_unix_timestamp()
    {
        $future = Carbon::now()->addMinutes(30);

        $token = $this->repo->store('access-abc', $future->timestamp, 'tenant-99', 'refresh-abc', 'id-abc');

        $this->assertEquals($future->timestamp, $token->expires_at->timestamp);
    }

    // ─── hasExpired() ─────────────────────────────────────────────────────────

    public function test_has_expired_returns_true_when_no_token_exists()
    {
        $this->assertTrue($this->repo->hasExpired());
    }

    public function test_has_expired_returns_false_when_token_is_valid()
    {
        $this->repo->store('access-abc', now()->addHour()->timestamp, 'tenant-99', 'refresh-abc', 'id-abc');

        $this->assertFalse($this->repo->hasExpired());
    }

    public function test_has_expired_returns_true_when_token_is_expired()
    {
        XeroToken::create([
            'access_token'  => 'access-old',
            'refresh_token' => 'refresh-old',
            'id_token'      => 'id-old',
            'tenant_id'     => 'tenant-99',
            'expires_at'    => Carbon::now()->subMinutes(5),
        ]);

        $this->assertTrue($this->repo->hasExpired());
    }
}
