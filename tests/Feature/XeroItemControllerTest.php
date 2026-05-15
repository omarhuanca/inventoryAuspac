<?php

namespace Tests\Feature;

use App\Modules\Product\Service\ProductService;
use App\Modules\Xero\Service\XeroItemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XeroItemControllerTest extends TestCase
{
    use RefreshDatabase;

    // ─── Valid product payload ────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        // We need real FK rows in DB because ProductRequest validates them
        $coin     = \App\Modules\Coin\Domain\Coin::create(['code' => 'USD']);
        $measure  = \App\Modules\Measure\Domain\Measure::create(['code' => 'UNIT']);
        $brand    = \App\Modules\Brand\Domain\Brand::create(['code' => 'BR-X']);
        $subBrand = \App\Modules\SubBrand\Domain\SubBrand::create(['code' => 'SB-X', 'brand_id' => $brand->id]);
        $supplier = \App\Modules\Supplier\Domain\Supplier::create(['name' => 'Sup-X']);

        return array_merge([
            'code'                => 'ITEM-001',
            'supplier_cost_price' => 50.00,
            'supplier_coin'       => ['id' => $coin->id],
            'landing_cost_price'  => 60.00,
            'landing_coin'        => ['id' => $coin->id],
            'retail_price'        => 100.00,
            'promotional_price'   => 80.00,
            'stock'               => 10,
            'measure'             => ['id' => $measure->id],
            'serial_tracking'     => 'SER-001',
            'dimension_size'      => '10x10x10',
            'dimension_weight'    => 2,
            'sub_brand'           => ['id' => $subBrand->id],
            'supplier'            => ['id' => $supplier->id],
        ], $overrides);
    }

    // ─── Validation ───────────────────────────────────────────────────────────

    public function test_store_returns_422_when_required_fields_are_missing()
    {
        $response = $this->postJson('/api/xero/items', []);

        $response->assertStatus(422);
    }

    public function test_store_returns_422_when_code_is_empty()
    {
        $this->mock(XeroItemService::class, fn($m) => $m->shouldReceive('pushProduct')->never());

        $response = $this->postJson('/api/xero/items', $this->validPayload(['code' => '']));

        $response->assertStatus(422);
    }

    // ─── Product already exists locally ──────────────────────────────────────

    public function test_store_returns_422_when_product_code_already_exists()
    {
        $payload = $this->validPayload();

        // Create it first via the standard endpoint
        $this->mock(XeroItemService::class, fn($m) => $m->shouldReceive('pushProduct')->once()->andReturn('xero-id-1'));
        $this->postJson('/api/xero/items', $payload)->assertStatus(201);

        // Try again with the same code
        $this->mock(XeroItemService::class, fn($m) => $m->shouldReceive('pushProduct')->never());
        $response = $this->postJson('/api/xero/items', $payload);

        $response->assertStatus(422)
            ->assertJson(['error' => true]);
    }

    // ─── Successful creation + Xero sync ─────────────────────────────────────

    public function test_store_creates_product_and_syncs_to_xero_successfully()
    {
        $this->mock(XeroItemService::class, function ($mock) {
            $mock->shouldReceive('pushProduct')
                ->once()
                ->andReturn('xero-item-id-abc');
        });

        $response = $this->postJson('/api/xero/items', $this->validPayload());

        $response->assertStatus(201)
            ->assertJson([
                'error'   => false,
                'message' => 'Product created and synced to Xero.',
                'data'    => [
                    'code'         => 'ITEM-001',
                    'xero_item_id' => 'xero-item-id-abc',
                ],
            ]);
    }

    public function test_store_persists_xero_item_id_in_database()
    {
        $this->mock(XeroItemService::class, function ($mock) {
            $mock->shouldReceive('pushProduct')->once()->andReturn('xero-item-id-abc');
        });

        $this->postJson('/api/xero/items', $this->validPayload());

        $this->assertDatabaseHas('product', [
            'code'         => 'ITEM-001',
            'xero_item_id' => 'xero-item-id-abc',
        ]);
    }

    // ─── Xero not connected ───────────────────────────────────────────────────

    public function test_store_returns_503_when_xero_is_not_connected()
    {
        $this->mock(XeroItemService::class, function ($mock) {
            $mock->shouldReceive('pushProduct')
                ->once()
                ->andThrow(new \RuntimeException('No Xero token found. Please authorize first via GET /api/xero/auth.'));
        });

        $response = $this->postJson('/api/xero/items', $this->validPayload());

        $response->assertStatus(503)
            ->assertJson([
                'error'   => true,
                'data'    => ['code' => 'ITEM-001'],  // product was saved locally
            ]);
    }

    public function test_store_saves_product_locally_even_when_xero_is_not_connected()
    {
        $this->mock(XeroItemService::class, function ($mock) {
            $mock->shouldReceive('pushProduct')
                ->once()
                ->andThrow(new \RuntimeException('No Xero token found. Please authorize first via GET /api/xero/auth.'));
        });

        $this->postJson('/api/xero/items', $this->validPayload());

        $this->assertDatabaseHas('product', ['code' => 'ITEM-001']);
    }

    // ─── Xero API error ───────────────────────────────────────────────────────

    public function test_store_returns_502_when_xero_api_throws_generic_exception()
    {
        $this->mock(XeroItemService::class, function ($mock) {
            $mock->shouldReceive('pushProduct')
                ->once()
                ->andThrow(new \Exception('Xero API unavailable.'));
        });

        $response = $this->postJson('/api/xero/items', $this->validPayload());

        $response->assertStatus(502)
            ->assertJson([
                'error' => true,
                'data'  => ['code' => 'ITEM-001'],
            ]);
    }
}
