<?php

namespace Tests\Unit;

use App\Modules\Brand\Domain\Brand;
use App\Modules\Bundle\Domain\Bundle;
use App\Modules\Coin\Domain\Coin;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Product\Domain\Product;
use App\Modules\SubBrand\Domain\SubBrand;
use App\Modules\Supplier\Domain\Supplier;
use PHPUnit\Framework\TestCase;

class BundleTest extends TestCase
{
    private Coin $coin;
    private array $products;
    protected function setUp(): void
    {
        parent::setUp();

        $this->coin = Coin::at('USD');

        $product1 = Product::at('P001', 100, $this->coin, 150, $this->coin, 200,
            180, 10, Measure::at('UNIT'), 'SER001', '10x10x10',
            5, SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01'));

        $product2 = Product::at('P002', 80, $this->coin, 100, $this->coin, 150,
            120, 20, Measure::at('UNIT'), 'SER002', '10x10x10',
            5, SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01'));

        $this->products = [$product1, $product2];
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

    public function test_can_create_valid_bundle()
    {
        [$product1, $product2] = $this->products;

        $bundle = Bundle::at('B001', 200, $this->coin, 300, 250, [$product1, $product2]);

        $this->assertCount(2, $bundle->getProducts());
        $this->assertEquals('P001', $bundle->getProducts()[0]->code);
        $this->assertEquals('P002', $bundle->getProducts()[1]->code);
    }

    public function test_code_cannot_be_empty()
    {
        [$product1, $product2] = $this->products;

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('', 100, $this->coin, 200, 150, [$product1, $product2]),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$codeEmpty, $e->getMessage())
        );
    }

    public function test_code_too_short_is_invalid()
    {
        [$product1, $product2] = $this->products;

        $this->shouldThrowAndAssert(
            fn() => Bundle::at(str_repeat('B', 51), 100, $this->coin, 200, 150, [$product1, $product2]),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$codeLength, $e->getMessage())
        );
    }

    public function test_code_too_long_is_invalid()
    {
        [$product1, $product2] = $this->products;

        $this->shouldThrowAndAssert(
            fn() => Bundle::at(str_repeat('B', 51), 100, $this->coin, 200, 150, [$product1, $product2]),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$codeLength, $e->getMessage())
        );
    }

    public function test_code_with_invalid_characters()
    {
        [$product1, $product2] = $this->products;

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('INVALID CODE!', 100, $this->coin, 200, 150, [$product1, $product2]),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$codeInvalid, $e->getMessage())
        );
    }

    public function test_landing_cost_must_be_non_negative_number()
    {
        [$product1, $product2] = $this->products;

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('B001', -100, $this->coin, 200, 150, [$product1, $product2]),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$landingCostInvalid, $e->getMessage())
        );
    }

    public function test_retail_price_must_be_non_negative_number()
    {
        [$product1, $product2] = $this->products;

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('B001', 100, $this->coin, -100, 50, [$product1, $product2]),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$retailInvalid, $e->getMessage())
        );
    }

    public function test_promotional_price_must_be_non_negative_number()
    {
        [$product1, $product2] = $this->products;

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('B001', 100, $this->coin, 100, -50, [$product1, $product2]),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$promotionalNegative, $e->getMessage())
        );
    }

    public function test_promotional_price_must_be_less_than_retail_price()
    {
        [$product1, $product2] = $this->products;

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('B001', 100, $this->coin, 200, 250, [$product1, $product2]),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$promotionalInvalid, $e->getMessage())
        );
    }

    public function test_bundle_cannot_add_duplicate_product()
    {
        $product = Product::at(
            'P001', 100, $this->coin, 150, $this->coin, 200,
            180, 10, Measure::at('UNIT'), 'SER001', '10x10x10',
            5, SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
        );

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('B001', 200, $this->coin, 300, 250, [$product, $product]),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$productsDuplicate, $e->getMessage())
        );
    }

    public function test_bundle_must_have_at_least_two_products_when_none_are_added()
    {
        $this->shouldThrowAndAssert(
            fn() => Bundle::at('B001', 200, $this->coin, 300, 250, []),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$productsMin, $e->getMessage())
        );
    }

    public function test_bundle_must_have_at_least_two_products_when_only_one_is_added()
    {
        $product = Product::at(
            'P001', 100, $this->coin, 150, $this->coin, 200,
            180, 10, Measure::at('UNIT'), 'SER001', '10x10x10',
            5, SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
        );

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('B001', 200, $this->coin, 300, 250, [$product]),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$productsMin, $e->getMessage())
        );
    }
}
