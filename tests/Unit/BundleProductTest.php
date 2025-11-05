<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Bundle;
use App\Models\BundleProduct;
use App\Models\Coin;
use App\Models\Measure;
use App\Models\Product;
use App\Models\SubBrand;
use App\Models\Supplier;
use PHPUnit\Framework\TestCase;

class BundleProductTest extends TestCase
{
    private Bundle $bundle;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $coin = Coin::at('USD');

        $product1 = Product::at(
            'P001', 100, $coin, 150, $coin, 200,
            180, 10, Measure::at('UNIT'), 'SER001', '10x10x10',
            5, SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
        );

        $product2 = Product::at(
            'P002', 90, $coin, 130, $coin, 160,
            140, 15, Measure::at('UNIT'), 'SER002', '10x10x10',
            3, SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
        );

        $this->bundle = Bundle::at('B001', 200, $coin, 300, 250, [$product1, $product2]);
        $this->product = $product1;
    }

    public function shouldThrowAndAssert($should, $exceptionType, $assertions)
    {
        try {
            $should->__invoke();
            $this->fail();
        } catch (\Throwable $exception) {
            $this->assertEquals($exceptionType, get_class($exception));
            $assertions->__invoke($exception);
        }
    }

    public function test_can_create_valid_bundle_product()
    {
        $bundleProduct = BundleProduct::at($this->bundle, $this->product);

        $this->assertInstanceOf(BundleProduct::class, $bundleProduct);
        $this->assertEquals($this->bundle->id, $bundleProduct->bundle_id);
        $this->assertEquals($this->product->id, $bundleProduct->product_id);
    }

    public function test_bundle_is_required()
    {
        $this->shouldThrowAndAssert(
            fn() => BundleProduct::at(null, $this->product),
            \TypeError::class,
            fn($e) => $this->assertStringContainsString('must be of type', $e->getMessage())
        );
    }

    public function test_product_is_required()
    {
        $this->shouldThrowAndAssert(
            fn() => BundleProduct::at($this->bundle, null),
            \TypeError::class,
            fn($e) => $this->assertStringContainsString('must be of type', $e->getMessage())
        );
    }
}
