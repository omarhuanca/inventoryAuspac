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

class BundleProductControllerTest extends TestCase
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

        $this->productService->createProduct($this->generateProduct('P001'));
        $this->productService->createProduct($this->generateProduct('P002'));
    }

    private function generateProduct(string $code): array
    {
        return [
            'code' => $code,
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
        ];
    }

    public function test_can_delete_a_bundle_with_its_associated_products()
    {
        $bundle = $this->bundleService->createBundle([
            'code' => 'B001',
            'landing_cost_price' => 300,
            'landing_coin' => ['id' => $this->coin->id],
            'retail_price' => 400,
            'promotional_price' => 350,
            'products' => [
                $this->generateProduct('P001'),
                $this->generateProduct('P002'),
            ],
        ]);

        $this->assertDatabaseHas('bundle', ['id' => $bundle->id]);

        $response = $this->deleteJson("/api/bundleproducts/{$bundle->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Bundle and its associated products deleted successfully.',
                'error' => false,
            ]);

        $this->assertDatabaseMissing('bundle', ['id' => $bundle->id]);

        $this->assertDatabaseMissing('bundle_product', ['bundle_id' => $bundle->id]);
    }

    public function test_returns_404_when_deleting_non_existent_bundle()
    {
        $response = $this->deleteJson('/api/bundleproducts/99');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Bundle not found.',
                'error' => true,
            ]);
    }
}
