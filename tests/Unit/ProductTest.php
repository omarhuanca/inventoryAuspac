<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Coin;
use App\Models\Measure;
use App\Models\Product;
use App\Models\SubBrand;
use App\Models\Supplier;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function shouldThrowAndAssert($should, $exceptionType, $assertions)
    {
        try {
            $should->__invoke();
            $this->fail();
        } catch (\Exception $exception) {
            $this->assertEquals($exceptionType, get_class($exception));
            $assertions->__invoke($exception);
        }
    }

    public function test_can_create_product()
    {
        $coin = Coin::at('USD');
        $measure = Measure::at('UNIT');
        $subBrand = SubBrand::at('ACME_ECO', Brand::at('ACME'));
        $supplier = Supplier::at('Supplier01');

        $product = Product::at(
            'P001', 100, $coin, $coin, 150, $coin, 200, 180,
            50, $measure, 'SER001', '10x10x10', 5, $subBrand, $supplier
        );

        $this->assertEquals('P001', $product->code);
        $this->assertEquals(100, $product->supplier_cost);
        $this->assertEquals($coin->id, $product->supplier_cost_price_id);
        $this->assertEquals($coin->id, $product->supplier_coin_id);
        $this->assertEquals(150, $product->landing_cost_price);
        $this->assertEquals($coin->id, $product->landing_coin_id);
        $this->assertEquals(200, $product->retail_price);
        $this->assertEquals(180, $product->promotional_price);
        $this->assertEquals(50, $product->stock);
        $this->assertEquals($measure->id, $product->measure_id);
        $this->assertEquals('SER001', $product->serial_tracking);
        $this->assertEquals('10x10x10', $product->dimension_size);
        $this->assertEquals(5, $product->dimension_weight);
        $this->assertEquals($subBrand->id, $product->sub_brand_id);
        $this->assertEquals($supplier->id, $product->supplier_id);
    }

    public function test_code_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            fn () => Product::at(
                '', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$codeEmpty, $e->getMessage())
        );
    }

    public function test_code_too_short_is_invalid()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$codeLength, $e->getMessage())
        );
    }

    public function test_code_too_long_is_invalid()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                str_repeat('P', 51), 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$codeLength, $e->getMessage())
        );
    }

    public function test_code_with_invalid_characters()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001@01', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$codeInvalid, $e->getMessage())
        );
    }

    public function test_supplier_cost_must_be_non_negative_number()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', -1, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$costInvalid, $e->getMessage())
        );
    }

    public function test_landing_cost_must_be_non_negative_number()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                -150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$landingInvalid, $e->getMessage())
        );
    }

    public function test_retail_price_must_be_non_negative_number()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), -1, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$retailInvalid, $e->getMessage())
        );
    }

    public function test_promotional_price_must_be_less_than_retail()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 250, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$promotionalInvalid, $e->getMessage())
        );
    }

    public function test_stock_must_be_non_negative_integer()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, -50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$stockInvalid, $e->getMessage())
        );
    }

    public function test_dimension_size_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            fn () => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), '', '', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$dimensionSizeEmpty, $e->getMessage())
        );
    }

    public function test_dimension_size_must_not_exceed_50_characters()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), 'SER001', str_repeat('A', 51), 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$dimensionInvalid, $e->getMessage())
        );
    }

    public function test_dimension_weight_must_be_non_negative_number()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', -5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$weightInvalid, $e->getMessage())
        );
    }

    public function test_serial_tracking_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            fn () => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), '', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$serialTrackingEmpty, $e->getMessage())
        );
    }

    public function test_serial_tracking_must_not_exceed_100_characters()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), str_repeat('S', 101), '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$serialInvalid, $e->getMessage())
        );
    }

    public function test_supplier_cost_price_must_be_valid()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, null, Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$relationInvalid, $e->getMessage())
        );
    }

    public function test_supplier_coin_must_be_valid()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, Coin::at('USD'), null,
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$relationInvalid, $e->getMessage())
        );
    }

    public function test_landing_coin_must_be_valid()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                150, null, 200, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$relationInvalid, $e->getMessage())
        );
    }

    public function test_measure_must_be_valid()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                null, 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$relationInvalid, $e->getMessage())
        );
    }

    public function test_sub_brand_must_be_valid()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                null, Supplier::at('Supplier01')
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$relationInvalid, $e->getMessage())
        );
    }

    public function test_supplier_must_be_valid()
    {
        $this->shouldThrowAndAssert(
            fn() => Product::at(
                'P001', 100, Coin::at('USD'), Coin::at('USD'),
                150, Coin::at('USD'), 200, 180, 50,
                Measure::at('UNIT'), 'SER001', '10x10x10', 5,
                SubBrand::at('ACME_ECO', Brand::at('ACME')), null
            ),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Product::$relationInvalid, $e->getMessage())
        );
    }

}
