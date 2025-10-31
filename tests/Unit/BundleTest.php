<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Bundle;
use App\Models\Coin;
use App\Models\Measure;
use App\Models\Product;
use App\Models\SubBrand;
use App\Models\Supplier;
use PHPUnit\Framework\TestCase;

class BundleTest extends TestCase
{
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
        $coin = Coin::at('USD');

        $product1 = Product::at('P001', 100, $coin, 150, $coin, 200,
            180, 10, Measure::at('UNIT'), 'SER001', '10x10x10',
            5, SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01'));

        $product2 = Product::at('P002', 80, $coin, 100, $coin, 150,
            120, 20, Measure::at('UNIT'), 'SER002', '10x10x10',
            5, SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01'));

        $bundle = Bundle::at('B001', 200, $coin, 300, 250);
        $bundle->addProduct($product1);
        $bundle->addProduct($product2);

        $this->assertCount(2, $bundle->getProducts());
        $this->assertEquals('P001', $bundle->getProducts()[0]->code);
        $this->assertEquals('P002', $bundle->getProducts()[1]->code);
    }

    public function test_code_cannot_be_empty()
    {
        $coin = Coin::at('USD');

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('', 100, $coin, 200, 150),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$codeEmpty, $e->getMessage())
        );
    }

    public function test_code_too_short_is_invalid()
    {
        $coin = Coin::at('USD');

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('B', 100, $coin, 200, 150),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$codeLength, $e->getMessage())
        );
    }

    public function test_code_too_long_is_invalid()
    {
        $coin = Coin::at('USD');

        $this->shouldThrowAndAssert(
            fn() => Bundle::at(str_repeat('B', 51), 100, $coin, 200, 150),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$codeLength, $e->getMessage())
        );
    }

    public function test_code_with_invalid_characters()
    {
        $coin = Coin::at('USD');

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('INVALID CODE!', 100, $coin, 200, 150),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$codeInvalid, $e->getMessage())
        );
    }

    public function test_landing_cost_must_be_non_negative_number()
    {
        $coin = Coin::at('USD');

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('B001', -100, $coin, 200, 150),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$landingCostInvalid, $e->getMessage())
        );
    }

    public function test_retail_price_must_be_non_negative_number()
    {
        $coin = Coin::at('USD');

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('B001', 100, $coin, -100, 50),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$retailInvalid, $e->getMessage())
        );
    }

    public function test_promotional_price_must_be_non_negative_number()
    {
        $coin = Coin::at('USD');

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('B001', 100, $coin, 100, -50),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$promotionalNegative, $e->getMessage())
        );
    }

    public function test_promotional_price_must_be_less_than_retail_price()
    {
        $coin = Coin::at('USD');

        $this->shouldThrowAndAssert(
            fn() => Bundle::at('B001', 100, $coin, 200, 250),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Bundle::$promotionalInvalid, $e->getMessage())
        );
    }

    public function test_bundle_cannot_add_duplicate_product()
    {
        $coin = Coin::at('USD');

        $product = Product::at(
            'P001', 100, $coin, 150, $coin, 200,
            180, 10, Measure::at('UNIT'), 'SER001', '10x10x10',
            5, SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
        );

        $bundle = Bundle::at('B001', 200, $coin, 300, 250);

        $this->shouldThrowAndAssert(
            function () use ($bundle, $product) {
                $bundle->addProduct($product);
                $bundle->addProduct($product);
            },
            \RuntimeException::class,
            function ($exception) use ($bundle) {
                $this->assertEquals(1, count($bundle->getProducts()));
            }
        );
    }

    public function test_bundle_must_have_at_least_two_products_when_none_are_added()
    {
        $coin = Coin::at('USD');

        $bundle = Bundle::at('B001', 200, $coin, 300, 250);

        $this->assertCount(0, $bundle->getProducts());

        $this->shouldThrowAndAssert(
            fn() => $bundle->ensureHasProducts(),
            \RuntimeException::class,
            function ($exception) use ($bundle) {
                $this->assertEquals(Bundle::$noProducts, $exception->getMessage());
                $this->assertEquals(0, count($bundle->getProducts()));

            }
        );
    }

    public function test_bundle_must_have_at_least_two_products_when_only_one_is_added()
    {
        $coin = Coin::at('USD');
        $product = Product::at('P001', 100, $coin, 150, $coin, 200,
            180, 10, Measure::at('UNIT'), 'SER001', '10x10x10',
            5, SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01'));

        $bundle = Bundle::at('B001', 200, $coin, 300, 250);
        $bundle->addProduct($product);

        $this->assertCount(1, $bundle->getProducts());

        $this->shouldThrowAndAssert(
            fn() => $bundle->ensureHasProducts(),
            \RuntimeException::class,
            function ($exception) use ($bundle) {
                $this->assertEquals(Bundle::$noProducts, $exception->getMessage());
                $this->assertEquals(1, count($bundle->getProducts()));
            }
        );
    }
}
