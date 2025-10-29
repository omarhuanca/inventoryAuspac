<?php

namespace Tests\Feature;

use App\Services\BrandService;
use App\Services\CoinService;
use App\Services\MeasureService;
use App\Services\ProductService;
use App\Services\SubBrandService;
use App\Services\SupplierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;
    private CoinService $coinService;
    private MeasureService $measureService;
    private SubBrandService $subBrandService;
    private SupplierService $supplierService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productService = app(ProductService::class);
        $this->coinService = app(CoinService::class);
        $this->measureService = app(MeasureService::class);
        $this->subBrandService = app(SubBrandService::class);
        $this->supplierService = app(SupplierService::class);
    }

    public function test_can_list_products()
    {
        $coin = $this->coinService->createCoin(['code' => 'USD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);

        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'ACME_ECO',
            'brand' => ['id' => $brand->id],
        ]);

        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $this->productService->createProduct([
            'code' => 'P001',
            'supplier_cost_price' => 150,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 200,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 250,
            'promotional_price' => 230,
            'stock' => 100,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ]);

        $this->productService->createProduct([
            'code' => 'P002',
            'supplier_cost_price' => 200,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 220,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 250,
            'promotional_price' => 240,
            'stock' => 5,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER002',
            'dimension_size' => '5x5x5',
            'dimension_weight' => 2,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'message' => 'Product list.',
                'error' => false,
            ]);
    }

    public function test_can_create_a_product()
    {
        $coin = $this->coinService->createCoin(['code' => 'USD']);
        $measure = $this->measureService->createMeasure(['code' => 'KG']);

        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'SUB_ACME_1',
            'brand' => ['id' => $brand->id],
        ]);

        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier 1']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Product created.',
                'statusCode' => 201,
                'error' => false,
            ]);

        $this->assertDatabaseHas('product', ['code' => 'P001']);
    }

    public function test_code_must_be_required()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);

        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'ACME_ECO',
            'brand' => ['id' => $brand->id],
        ]);

        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => '',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field is required.']]);
    }

    public function test_code_must_be_string()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'ACME_ECO',
            'brand' => ['id' => $brand->id],
        ]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 12345,
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field must be a string.']]);
    }

    public function test_code_min_length()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'ACME_ECO',
            'brand' => ['id' => $brand->id],
        ]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'A',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field must be at least 2 characters.']]);
    }

    public function test_code_max_length()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'ACME_ECO',
            'brand' => ['id' => $brand->id],
        ]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => str_repeat('A', 51),
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field must not be greater than 50 characters.']]);
    }

    public function test_code_regex_validation()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'ACME_ECO',
            'brand' => ['id' => $brand->id],
        ]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'INVALID!',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field format is invalid.']]);
    }

    public function test_cannot_create_duplicate_product_code()
    {
        $coin = $this->coinService->createCoin(['code' => 'USD']);
        $measure = $this->measureService->createMeasure(['code' => 'KG']);

        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'SUB_ACME_1',
            'brand' => ['id' => $brand->id],
        ]);

        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier 1']);

        $data = [
            'code' => 'PROD_DUP',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'ABC123',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $this->postJson('/api/products', $data)
            ->assertStatus(201);

        $this->postJson('/api/products', $data)
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Error creating Product: Product code already exists.',
                'error' => true,
            ]);
    }

    public function test_supplier_cost_price_must_be_required()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => null,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The supplier cost price field is required.']]);
    }

    public function test_supplier_cost_price_must_be_numeric_invalid_string()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 'abc',
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
    }

    public function test_supplier_cost_price_must_be_min_0_invalid_negative()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => -10,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
    }

    public function test_landing_cost_price_must_be_required()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => null,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The landing cost price field is required.']]);
    }

    public function test_landing_cost_price_must_be_numeric_invalid_string()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 'abc',
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The landing cost price field must be a number.']]);
    }

    public function test_landing_cost_price_must_be_min_0_invalid_negative()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => -1,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The landing cost price field must be at least 0.']]);
    }

    public function test_retail_price_must_be_required()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => null,
            'promotional_price' => 100,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['The retail price field is required.']);
    }

    public function test_retail_price_must_be_numeric_invalid_string()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 'abc',
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['The retail price field must be a number.']);
    }

    public function test_retail_price_must_be_min_0_invalid_negative()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => -1,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['The retail price field must be at least 0.']);
    }

    public function test_promotional_price_must_be_required()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 100,
            'promotional_price' => null,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['The promotional price field is required.']);
    }

    public function test_promotional_price_must_be_numeric_invalid_string()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 'abc',
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['The promotional price field must be a number.']);
    }

    public function test_promotional_price_lt_retail_price()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'ACME_ECO',
            'brand' => ['id' => $brand->id],
        ]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 100,
            'promotional_price' => 120,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The promotional price field must be less than 100.']]);
    }


    public function test_stock_must_be_required()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => null,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The stock field is required.']]);
    }

    public function test_stock_must_be_integer_invalid_string()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 'abc',
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The stock field must be an integer.']]);
    }

    public function test_stock_must_be_min_0_invalid_negative()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => -5,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The stock field must be at least 0.']]);
    }

    public function test_serial_tracking_must_be_required()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => '',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The serial tracking field is required.']]);
    }

    public function test_serial_tracking_must_be_string_invalid_integer()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 12345,
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The serial tracking field must be a string.']]);
    }

    public function test_serial_tracking_must_not_exceed_100_characters()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => str_repeat('A', 101),
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The serial tracking field must not be greater than 100 characters.']]);
    }

    public function test_dimension_size_must_be_required()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The dimension size field is required.']]);
    }

    public function test_dimension_size_must_be_string_invalid_integer()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => 12345,
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The dimension size field must be a string.']]);
    }

    public function test_dimension_size_must_not_exceed_50_characters()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => str_repeat('A', 51),
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The dimension size field must not be greater than 50 characters.']]);
    }

    public function test_dimension_weight_must_be_required()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => null,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The dimension weight field is required.']]);
    }

    public function test_dimension_weight_must_be_integer_invalid_string()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 'abc',
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The dimension weight field must be an integer.']]);
    }

    public function test_dimension_weight_must_be_min_0_invalid_negative()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand(['code' => 'ACME_ECO','brand' => ['id' => $brand->id]]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => -1,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->postJson('/api/products', $data);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The dimension weight field must be at least 0.']]);
    }

    public function test_can_show_product_by_id()
    {
        $coin = $this->coinService->createCoin(['code' => 'USD']);
        $measure = $this->measureService->createMeasure(['code' => 'KG']);

        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'SUB_ACME_1',
            'brand' => ['id' => $brand->id],
        ]);

        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier 1']);

        $product = $this->productService->createProduct([
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ]);

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product found.',
                'error' => false,
            ]);

        $this->assertDatabaseHas('product', ['code' => 'P001']);
    }

    public function test_returns_404_when_showing_non_existent_product()
    {
        $response = $this->getJson('/api/products/99');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found.',
                'error' => true,
            ]);
    }

    public function test_can_update_product()
    {
        $coin = $this->coinService->createCoin(['code' => 'USD']);
        $measure = $this->measureService->createMeasure(['code' => 'KG']);

        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'SUB_ACME_1',
            'brand' => ['id' => $brand->id],
        ]);

        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier 1']);

        $product = $this->productService->createProduct([
            'code' => 'OLD_CODE',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'OLD123',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ]);

        $updateData = [
            'code' => 'NEW_CODE',
            'supplier_cost_price' => 200,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 220,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 250,
            'promotional_price' => 240,
            'stock' => 20,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'NEW123',
            'dimension_size' => '20x20x20',
            'dimension_weight' => 10,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product updated.',
                'statusCode' => 200,
                'error' => false,
            ]);

        $this->assertDatabaseHas('product', ['code' => 'NEW_CODE']);
    }

    public function test_cannot_update_product_to_duplicate_code()
    {
        $coin = $this->coinService->createCoin(['code' => 'USD']);
        $measure = $this->measureService->createMeasure(['code' => 'KG']);

        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'SUB_ACME_1',
            'brand' => ['id' => $brand->id],
        ]);

        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier 1']);

        $product1 = $this->productService->createProduct([
            'code' => 'PROD1',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'ABC123',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ]);

        $product2 = $this->productService->createProduct([
            'code' => 'PROD2',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'DEF123',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ]);

        $response = $this->putJson("/api/products/{$product2->id}", [
            'code' => 'PROD1',
            'supplier_cost_price' => 200,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 220,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 250,
            'promotional_price' => 240,
            'stock' => 20,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'NEW123',
            'dimension_size' => '20x20x20',
            'dimension_weight' => 10,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Error updating Product: Product code already exists.',
                'statusCode' => 422,
                'error' => true,
            ]);

        $this->assertDatabaseHas('product', ['code' => 'PROD2']);
        $this->assertDatabaseCount('product', 2);
    }

    public function test_returns_404_when_updating_non_existent_product()
    {
        $coin = $this->coinService->createCoin(['code' => 'USD']);
        $measure = $this->measureService->createMeasure(['code' => 'KG']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'SUB_ACME_1',
            'brand' => ['id' => $brand->id],
        ]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier 1']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => $coin->id],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => $coin->id],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => $measure->id],
            'serial_tracking' => 'ABC123',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => $subBrand->id],
            'supplier' => ['id' => $supplier->id],
        ];

        $response = $this->putJson('/api/products/99', $data);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Product not found.',
                'error' => true,
            ]);
    }

    public function test_product_related_entities_must_exist()
    {
        $coin = $this->coinService->createCoin(['code' => 'AUD']);
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $brand = app(BrandService::class)->createBrand(['code' => 'ACME']);
        $subBrand = $this->subBrandService->createSubBrand([
            'code' => 'ACME_ECO',
            'brand' => ['id' => $brand->id],
        ]);
        $supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);

        $data = [
            'code' => 'P001',
            'supplier_cost_price' => 100,
            'supplier_coin' => ['id' => 999],
            'landing_cost_price' => 120,
            'landing_coin' => ['id' => 999],
            'retail_price' => 150,
            'promotional_price' => 140,
            'stock' => 10,
            'measure' => ['id' => 999],
            'serial_tracking' => 'SER001',
            'dimension_size' => '10x10x10',
            'dimension_weight' => 5,
            'sub_brand' => ['id' => 999],
            'supplier' => ['id' => 999],
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(422);
        $response->assertJsonFragment(['You have not selected a valid supplier coin.']);
        $response->assertJsonFragment(['You have not selected a valid landing coin.']);
        $response->assertJsonFragment(['You have not selected a valid measure.']);
        $response->assertJsonFragment(['You have not selected a valid sub brand.']);
        $response->assertJsonFragment(['You have not selected a valid supplier.']);
    }
}
