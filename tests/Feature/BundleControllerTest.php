<?php

namespace Tests\Feature;

use App\Services\BrandService;
use App\Services\BundleService;
use App\Services\CoinService;
use App\Services\MeasureService;
use App\Services\ProductService;
use App\Services\SubBrandService;
use App\Services\SupplierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BundleControllerTest extends TestCase
{
    use RefreshDatabase;

    private BundleService $bundleService;
    private ProductService $productService;
    private CoinService $coinService;
    private MeasureService $measureService;
    private SubBrandService $subBrandService;
    private SupplierService $supplierService;
    private BrandService $brandService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bundleService = app(BundleService::class);
        $this->productService = app(ProductService::class);
        $this->coinService = app(CoinService::class);
        $this->measureService = app(MeasureService::class);
        $this->subBrandService = app(SubBrandService::class);
        $this->supplierService = app(SupplierService::class);
        $this->brandService = app(BrandService::class);

        $this->coin = $this->coinService->createCoin(['code' => 'USD']);
        $this->measure = $this->measureService->createMeasure(['code' => 'UNIT']);
        $this->supplier = $this->supplierService->createSupplier(['name' => 'Supplier01']);
        $this->brand = $this->brandService->createBrand(['code' => 'ACME']);
        $this->subBrand = $this->subBrandService->createSubBrand([
            'code' => 'ACME_ECO',
            'brand' => ['id' => $this->brand->id],
        ]);

        foreach (['P001', 'P002', 'P003', 'P004'] as $code) {
            $this->productService->createProduct([
                'code' => $code,
                'supplier_cost_price' => 100,
                'supplier_coin' => ['id' => $this->coin->id],
                'landing_cost_price' => 120,
                'landing_coin' => ['id' => $this->coin->id],
                'retail_price' => 150,
                'promotional_price' => 140,
                'stock' => 10,
                'measure' => ['id' => $this->measure->id],
                'serial_tracking' => 'SER_' . $code,
                'dimension_size' => '10x10x10',
                'dimension_weight' => 5,
                'sub_brand' => ['id' => $this->subBrand->id],
                'supplier' => ['id' => $this->supplier->id],
            ]);
        }
    }

    public function test_can_list_bundles()
    {
        $this->bundleService->createBundle([
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ]);

        $this->bundleService->createBundle([
            'code' => 'B002',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
                    'code' => 'P003',
                    'supplier_cost_price' => 90,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 110,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 15,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER003',
                    'dimension_size' => '8x8x8',
                    'dimension_weight' => 4,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
                [
                    'code' => 'P004',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 160,
                    'promotional_price' => 150,
                    'stock' => 20,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER004',
                    'dimension_size' => '9x9x9',
                    'dimension_weight' => 6,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ]);

        $response = $this->getJson('/api/bundles');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'message' => 'Bundle list.',
                'error' => false,
                ]);
    }

    public function test_can_create_a_bundle()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Bundle created.',
                'statusCode' => 201,
                'error' => false,
            ]);

        $this->assertDatabaseHas('bundle', ['code' => 'B001']);
    }

    public function test_code_must_be_required()
    {
        $data = [
            'code' => '',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field is required.']]);
    }

    public function test_code_must_be_string()
    {
        $data = [
            'code' => 12345,
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field must be a string.']]);
    }

    public function test_code_min_length()
    {
        $data = [
            'code' => 'B',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field must be at least 2 characters.']]);
    }

    public function test_code_max_length()
    {
        $data = [
            'code' => str_repeat('B', 51),
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field must not be greater than 50 characters.']]);
    }

    public function test_code_regex_validation()
    {
        $data = [
            'code' => 'B001!',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field format is invalid.']]);
    }

    public function test_landing_cost_price_must_be_required()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => null,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The landing cost price field is required.']]);
    }

    public function test_landing_cost_price_must_be_numeric_invalid_string()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => 'abc',
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The landing cost price field must be a number.']]);
    }

    public function test_landing_cost_price_must_be_min_0_invalid_negative()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => -1,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The landing cost price field must be at least 0.']]);
    }

    public function test_retail_price_must_be_required()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => null,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['The retail price field is required.']);
    }

    public function test_retail_price_must_be_numeric_invalid_string()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 'abc',
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['The retail price field must be a number.']);
    }

    public function test_retail_price_must_be_min_0_invalid_negative()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => -400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['The retail price field must be at least 0.']);
    }

    public function test_promotional_price_must_be_required()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => null,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['The promotional price field is required.']);
    }

    public function test_promotional_price_must_be_numeric_invalid_string()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 'abc',
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['The promotional price field must be a number.']);
    }

    public function test_promotional_price_must_be_min_0_invalid_negative()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => -100,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['The promotional price field must be at least 0.']);
    }

    public function test_promotional_price_less_than_retail_price()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 500,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->postJson('/api/bundles', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The promotional price field must be less than 400.']]);
    }

    public function test_cannot_create_duplicate_bundle_code()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $this->postJson('/api/bundles', $data)
            ->assertStatus(201);

        $this->postJson('/api/bundles', $data)
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Error creating Bundle: Bundle code already exists.',
                'error' => true,
            ]);
    }

    public function test_cannot_create_bundle_with_less_than_two_products()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                ]
            ],
        ];

        $this->postJson('/api/bundles', $data)
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Validation error.',
                'data' => ['A bundle must contain at least two products.'],
                'error' => true,
            ]);
    }

    public function test_cannot_create_bundle_with_duplicate_products()
    {
        $data = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P001',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $this->postJson('/api/bundles', $data)
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Error creating Bundle: Duplicate product in bundle is not allowed.',
                'error' => true,
            ]);
    }


    public function test_can_show_bundle_by_id()
    {
        $bundle = $this->bundleService->createBundle([
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ]);

        $response = $this->getJson("/api/bundles/{$bundle->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Bundle found.',
                'error' => false,
            ]);

        $this->assertDatabaseHas('bundle', ['code' => 'B001']);
    }

    public function test_returns_404_when_showing_non_existent_bundle()
    {
        $response = $this->getJson('/api/bundles/99');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Bundle not found.',
                'error' => true,
            ]);
    }

    public function test_can_update_bundle()
    {
        $createData = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $createResponse = $this->postJson('/api/bundles', $createData)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Bundle created.',
                'error' => false,
            ]);

        $bundleId = $createResponse->json('data.id');

        $updateData = [
            'code' => 'B002',
            'landing_cost_price' => 350,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 450,
            'promotional_price' => 390,
            'products' => [
                [
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
                [
                    'code' => 'P003',
                    'supplier_cost_price' => 110,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 130,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 160,
                    'promotional_price' => 150,
                    'stock' => 20,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER003',
                    'dimension_size' => '20x20x20',
                    'dimension_weight' => 10,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $updateResponse = $this->putJson("/api/bundles/{$bundleId}", $updateData);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Bundle updated.',
                'error' => false,
            ]);

        $this->assertDatabaseHas('bundle', [
            'id' => $bundleId,
            'code' => 'B002',
        ]);
    }

    public function test_cannot_update_bundle_to_duplicate_code()
    {
        $bundle1 = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $bundle1Response = $this->postJson('/api/bundles', $bundle1)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Bundle created.',
                'error' => false,
            ]);

        $bundle2 = [
            'code' => 'B002',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
                    'code' => 'P003',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER003',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
                [
                    'code' => 'P004',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER004',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $bundle2Response = $this->postJson('/api/bundles', $bundle2)
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Bundle created.',
                'error' => false,
            ]);

        $bundle2Id = $bundle2Response->json('data.id');

        $updateData = [
            'code' => 'B001',
            'landing_cost_price' => 350,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 450,
            'promotional_price' => 390,
            'products' => $bundle2['products'],
        ];

        $response = $this->putJson("/api/bundles/{$bundle2Id}", $updateData);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Error updating Bundle: Bundle code already exists.',
                'statusCode' => 422,
                'error' => true,
            ]);

        $this->assertDatabaseHas('bundle', ['code' => 'B002']);
        $this->assertDatabaseCount('bundle', 2);
    }

    public function test_cannot_update_bundle_with_less_than_two_products()
    {
        $bundle = $this->bundleService->createBundle([
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ]);

        $data = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                ]
            ],
        ];

        $this->putJson("/api/bundles/{$bundle->id}", $data)
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Validation error.',
                'data' => ['A bundle must contain at least two products.'],
                'error' => true,
            ]);
    }

    public function test_cannot_update_bundle_with_duplicate_products()
    {
        $bundle = $this->bundleService->createBundle([
            'code' => 'B002',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ]);

        $data = [
            'code' => 'B002',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P001',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $this->putJson("/api/bundles/{$bundle->id}", $data)
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Error updating Bundle: Duplicate product in bundle is not allowed.',
                'error' => true,
            ]);
    }

    public function test_returns_404_when_updating_non_existent_bundle()
    {
        $updateData = [
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                [
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
                [
                    'code' => 'P002',
                    'supplier_cost_price' => 100,
                    'supplier_coin' => ['id' => $this->coin->id],
                    'landing_cost_price' => 120,
                    'landing_coin' => ['id' => $this->coin->id],
                    'retail_price' => 150,
                    'promotional_price' => 140,
                    'stock' => 10,
                    'measure' => ['id' => $this->measure->id],
                    'serial_tracking' => 'SER002',
                    'dimension_size' => '10x10x10',
                    'dimension_weight' => 5,
                    'sub_brand' => ['id' => $this->subBrand->id],
                    'supplier' => ['id' => $this->supplier->id],
                ],
            ],
        ];

        $response = $this->putJson('/api/bundles/99', $updateData);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Bundle not found.',
                'error' => true,
            ]);
    }
}
