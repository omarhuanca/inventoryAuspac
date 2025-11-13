<?php

namespace Tests\Feature;

use App\Modules\Brand\Service\BrandService;
use App\Modules\Coin\Service\CoinService;
use App\Modules\Measure\Service\MeasureService;
use App\Modules\Product\Service\ProductService;
use App\Modules\StockBuy\Service\StockBuyService;
use App\Modules\SubBrand\Service\SubBrandService;
use App\Modules\Supplier\Service\SupplierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockBuyControllerTest extends TestCase
{
    use RefreshDatabase;

    private StockBuyService $stockBuyService;
    private ProductService $productService;
    private CoinService $coinService;
    private MeasureService $measureService;
    private SupplierService $supplierService;
    private BrandService $brandService;
    private SubBrandService $subBrandService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockBuyService = app(StockBuyService::class);
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
            'stock' => 10,
            'measure' => ['id' => $this->measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $this->subBrand->id],
            'supplier' => ['id' => $this->supplier->id],
        ]);
    }

    public function test_can_list_stock_buys()
    {
        $this->stockBuyService->createStockBuy([
            'product' => ['code' => $this->product->code],
            'amount' => 10,
            'date' => '2025-11-05',
            'description' => 'Initial stock',
        ]);

        $this->stockBuyService->createStockBuy([
            'product' => ['code' => $this->product->code],
            'amount' => 5,
            'date' => '2025-11-06',
            'description' => 'Second stock',
        ]);

        $response = $this->getJson('/api/stockbuys');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'message' => 'StockBuy list.',
                'error' => false,
            ]);
    }

    public function test_can_create_a_stock_buy()
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
            'description' => 'Purchase #001',
        ];

        $response = $this->postJson('/api/stockbuys', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'StockBuy created.',
                'statusCode' => 201,
                'error' => false,
            ]);

        $this->assertDatabaseHas('stock_buy', [
            'amount' => 15,
            'description' => 'Purchase #001',
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
            'description' => 'Purchase #001',
        ];

        $response = $this->postJson('/api/stockbuys', $data);

        $response->assertStatus(422)
            ->assertJson([
                'message' => "Error creating StockBuy: Product with code 'P0099' not found.",
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
                'stock' => 10,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => null,
            'date' => '2025-11-05',
            'description' => 'Purchase #001',
        ];

        $response = $this->postJson('/api/stockbuys', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The amount field is required.']]);
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
                'stock' => 10,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 'abc',
            'date' => '2025-11-05',
            'description' => 'Purchase #001',
        ];

        $response = $this->postJson('/api/stockbuys', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The amount field must be an integer.']]);
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
                'stock' => 10,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => -1,
            'date' => '2025-11-05',
            'description' => 'Purchase #001',
        ];

        $response = $this->postJson('/api/stockbuys', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['Amount must be greater than zero.']]);
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
                'stock' => 10,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 10,
            'date' => null,
            'description' => 'Purchase #001',
        ];

        $response = $this->postJson('/api/stockbuys', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The date field is required.']]);
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
                'stock' => 10,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 10,
            'date' => '11/05/2025',
            'description' => 'Purchase #001',
        ];

        $response = $this->postJson('/api/stockbuys', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['Date must be in format YYYY-mm-dd.']]);
    }

    public function test_description_must_be_required()
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
            'amount' => 10,
            'date' => '2025-11-05',
            'description' => '',
        ];

        $response = $this->postJson('/api/stockbuys', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The description field is required.']]);
    }

    public function test_description_must_be_string()
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
            'amount' => 10,
            'date' => '2025-11-05',
            'description' => 12345,
        ];

        $response = $this->postJson('/api/stockbuys', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The description field must be a string.']]);
    }

    public function test_description_must_not_exceed_255_characters()
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
            'amount' => 10,
            'date' => '2025-11-05',
            'description' => str_repeat('a', 256),
        ];

        $response = $this->postJson('/api/stockbuys', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The description field must not be greater than 255 characters.']]);
    }

    public function test_can_show_stock_buy_by_id()
    {
        $stockBuy = $this->stockBuyService->createStockBuy([
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
            'amount' => 20,
            'date' => '2025-11-05',
            'description' => 'Initial import',
        ]);

        $response = $this->getJson("/api/stockbuys/{$stockBuy->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'StockBuy found.',
                'error' => false,
            ]);

        $this->assertDatabaseHas('stock_buy', ['id' => $stockBuy->id]);
    }

    public function test_returns_404_when_showing_non_existent_stockbuy()
    {
        $response = $this->getJson('/api/stockbuys/99');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'StockBuy not found.',
                'error' => true,
            ]);
    }

    public function test_can_update_stock_buy()
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
                'stock' => 10,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 10,
            'date' => '2025-11-05',
            'description' => 'Initial purchase',
        ];

        $createResponse = $this->postJson('/api/stockbuys', $createData)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'StockBuy created.',
                'error' => false,
            ]);

        $stockBuyId = $createResponse->json('data.id');

        $updateData = [
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
            'amount' => 25,
            'date' => '2025-11-06',
            'description' => 'Updated purchase',
        ];

        $updateResponse = $this->putJson("/api/stockbuys/{$stockBuyId}", $updateData);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'message' => 'StockBuy updated.',
                'error' => false,
            ]);

        $this->assertDatabaseHas('stock_buy', [
            'id' => $stockBuyId,
            'amount' => 25,
            'description' => 'Updated purchase',
        ]);
    }

    public function test_returns_404_when_updating_non_existent_stock_buy()
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
                'stock' => 10,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER001',
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ],
            'amount' => 20,
            'date' => '2025-11-05',
            'description' => 'Updated purchase',
        ];

        $response = $this->putJson('/api/stockbuys/99', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'StockBuy not found.',
                'error' => true,
            ]);
    }

}
