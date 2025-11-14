<?php

namespace Tests\Feature;

use App\Modules\Brand\Service\BrandService;
use App\Modules\Coin\Service\CoinService;
use App\Modules\Measure\Service\MeasureService;
use App\Modules\Product\Service\ProductService;
use App\Modules\StockReferral\Service\StockReferralService;
use App\Modules\SubBrand\Service\SubBrandService;
use App\Modules\Supplier\Service\SupplierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StockReferralControllerTest extends TestCase
{
    use RefreshDatabase;

    private StockReferralService $stockReferralService;
    private ProductService $productService;
    private CoinService $coinService;
    private MeasureService $measureService;
    private SupplierService $supplierService;
    private BrandService $brandService;
    private SubBrandService $subBrandService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockReferralService = app(StockReferralService::class);
        $this->productService = app(ProductService::class);
        $this->coinService = app(CoinService::class);
        $this->measureService = app(MeasureService::class);
        $this->supplierService = app(SupplierService::class);
        $this->brandService = app(BrandService::class);
        $this->subBrandService = app(SubBrandService::class);

        $this->coin = $this->coinService->createCoin(['code' => 'USD']);
        $this->measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $this->supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);
        $this->brand = $this->brandService->createBrand(['code' => 'ACME']);
        $this->subBrand = $this->subBrandService->createSubBrand([
            'code' => 'ACME_ECO',
            'brand' => ['id' => $this->brand->id],
        ]);

        $this->product = $this->productService->createProduct([
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $this->coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 50,
            'measure' => ['id' => $this->measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $this->subBrand->id],
            'supplier' => ['id' => $this->supplier->id],
        ]);
    }

    public function test_can_list_stock_referrals()
    {
        $this->stockReferralService->createStockReferral([
            'product' => ['code' => $this->product->code],
            'amount' => 8,
            'date' => '2025-11-05',
        ]);

        $this->stockReferralService->createStockReferral([
            'product' => ['code' => $this->product->code],
            'amount' => 5,
            'date' => '2025-11-06',
        ]);

        $response = $this->getJson('/api/stockreferrals');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'message' => 'StockReferral list.',
                'error' => false,
            ]);
    }

    public function test_can_create_a_stock_referral()
    {
        $data = [
            'product' => [
                'code' => 'P001',
                'supplier_cost_price' => 100,
                'supplier_coin' => ['id' => $this->coin->id],
                'landing_cost_price' => 120,
                'landing_coin' => ['id' => $this->coin->id],
                'retail_price' => 150,
                'promotional_price' => 140,
                'stock' => 10,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 15,
            'date' => '2025-11-05',
        ];

        $response = $this->postJson('/api/stockreferrals', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'StockReferral created.',
                'statusCode' => 201,
                'error' => false,
            ]);

        $this->assertDatabaseHas('stock_referral', [
            'amount' => 15,
        ]);
    }

    public function test_product_must_exist()
    {
        $data = [
            'product' => [
                'code' => 'P0099',
                'supplier_cost_price' => 100,
                'supplier_coin' => ['id' => $this->coin->id],
                'landing_cost_price' => 120,
                'landing_coin' => ['id' => $this->coin->id],
                'retail_price' => 150,
                'promotional_price' => 140,
                'stock' => 10,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 15,
            'date' => '2025-11-05',
        ];

        $response = $this->postJson('/api/stockreferrals', $data);

        $response->assertStatus(422)
            ->assertJson([
                'message' => "Error creating StockReferral: Product with code 'P0099' not found.",
                'error' => true,
            ]);
    }

    public function test_amount_must_be_required()
    {
        $data = [
            'product' => [
                'code' => 'P001',
                'supplier_cost_price' => 100,
                'supplier_coin' => ['id' => $this->coin->id],
                'landing_cost_price' => 120,
                'landing_coin' => ['id' => $this->coin->id],
                'retail_price' => 150,
                'promotional_price' => 140,
                'stock' => 50,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => null,
            'date' => '2025-11-05',
        ];

        $response = $this->postJson('/api/stockreferrals', $data);

        $response->assertStatus(422)
            ->assertJsonFragment(['data' => ['The amount field is required.']]);
    }

    public function test_amount_must_be_numeric_invalid_string()
    {
        $data = [
            'product' => [
                'code' => 'P001',
                'supplier_cost_price' => 100,
                'supplier_coin' => ['id' => $this->coin->id],
                'landing_cost_price' => 120,
                'landing_coin' => ['id' => $this->coin->id],
                'retail_price' => 150,
                'promotional_price' => 140,
                'stock' => 50,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 'abc',
            'date' => '2025-11-05',
        ];

        $response = $this->postJson('/api/stockreferrals', $data);

        $response->assertStatus(422)
            ->assertJsonFragment(['data' => ['The amount field must be an integer.']]);
    }

    public function test_amount_must_be_min_1_invalid_negative()
    {
        $data = [
            'product' => [
                'code' => 'P001',
                'supplier_cost_price' => 100,
                'supplier_coin' => ['id' => $this->coin->id],
                'landing_cost_price' => 120,
                'landing_coin' => ['id' => $this->coin->id],
                'retail_price' => 150,
                'promotional_price' => 140,
                'stock' => 50,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => -1,
            'date' => '2025-11-05',
        ];

        $response = $this->postJson('/api/stockreferrals', $data);

        $response->assertStatus(422)
            ->assertJsonFragment(['data' => ['Amount must be greater than zero.']]);
    }

    public function test_date_must_be_required()
    {
        $data = [
            'product' => [
                'code' => 'P001',
                'supplier_cost_price' => 100,
                'supplier_coin' => ['id' => $this->coin->id],
                'landing_cost_price' => 120,
                'landing_coin' => ['id' => $this->coin->id],
                'retail_price' => 150,
                'promotional_price' => 140,
                'stock' => 50,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 10,
            'date' => null,
        ];

        $response = $this->postJson('/api/stockreferrals', $data);

        $response->assertStatus(422)
            ->assertJsonFragment(['data' => ['The date field is required.']]);
    }

    public function test_date_must_have_valid_format()
    {
        $data = [
            'product' => [
                'code' => 'P001',
                'supplier_cost_price' => 100,
                'supplier_coin' => ['id' => $this->coin->id],
                'landing_cost_price' => 120,
                'landing_coin' => ['id' => $this->coin->id],
                'retail_price' => 150,
                'promotional_price' => 140,
                'stock' => 50,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 10,
            'date' => '11/05/2025',
        ];

        $response = $this->postJson('/api/stockreferrals', $data);

        $response->assertStatus(422)
            ->assertJsonFragment(['data' => ['Date must be in format YYYY-mm-dd.']]);
    }

    public function test_can_show_stock_referral_by_id()
    {
        $stockReferral = $this->stockReferralService->createStockReferral([
            'product' => ['code' => 'P001'],
            'amount' => 20,
            'date' => '2025-11-05',
        ]);

        $response = $this->getJson("/api/stockreferrals/{$stockReferral->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'StockReferral found.',
                'error' => false,
            ]);

        $this->assertDatabaseHas('stock_referral', ['id' => $stockReferral->id]);
    }

    public function test_returns_404_when_showing_non_existent_stock_referral()
    {
        $response = $this->getJson('/api/stockreferrals/99');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'StockReferral not found.',
                'error' => true,
            ]);
    }

    public function test_can_update_stock_referral()
    {
        $createData = [
            'product' => [
                'code' => 'P001',
                'supplier_cost_price' => 100,
                'supplier_coin' => ['id' => $this->coin->id],
                'landing_cost_price' => 120,
                'landing_coin' => ['id' => $this->coin->id],
                'retail_price' => 150,
                'promotional_price' => 140,
                'stock' => 50,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 10,
            'date' => '2025-11-05',
        ];

        $createResponse = $this->postJson('/api/stockreferrals', $createData)
            ->assertStatus(201);

        $stockReferralId = $createResponse->json('data.id');

        $updateData = [
            'product' => [
                'code' => 'P001',
                'supplier_cost_price' => 100,
                'supplier_coin' => ['id' => $this->coin->id],
                'landing_cost_price' => 120,
                'landing_coin' => ['id' => $this->coin->id],
                'retail_price' => 150,
                'promotional_price' => 140,
                'stock' => 50,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 25,
            'date' => '2025-11-06',
        ];

        $updateResponse = $this->putJson("/api/stockreferrals/{$stockReferralId}", $updateData);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'message' => 'StockReferral updated.',
                'error' => false,
            ]);

        $this->assertDatabaseHas('stock_referral', [
            'id' => $stockReferralId,
            'amount' => 25,
        ]);
    }

    public function test_returns_404_when_updating_non_existent_stock_referral()
    {
        $updateData = [
            'product' => [
                'code' => 'P001',
                'supplier_cost_price' => 100,
                'supplier_coin' => ['id' => $this->coin->id],
                'landing_cost_price' => 120,
                'landing_coin' => ['id' => $this->coin->id],
                'retail_price' => 150,
                'promotional_price' => 140,
                'stock' => 50,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 10,
            'date' => '2025-11-05',
        ];

        $response = $this->putJson('/api/stockreferrals/99', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'StockReferral not found.',
                'error' => true,
            ]);
    }
}
