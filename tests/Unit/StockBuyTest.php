<?php

namespace Tests\Unit;

use App\Modules\Brand\Domain\Brand;
use App\Modules\Coin\Domain\Coin;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Product\Domain\Product;
use App\Modules\StockBuy\Domain\StockBuy;
use App\Modules\SubBrand\Domain\SubBrand;
use App\Modules\Supplier\Domain\Supplier;
use PHPUnit\Framework\TestCase;

class StockBuyTest extends TestCase
{
    private Coin $coin;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->coin = Coin::at('USD');

        $this->product = Product::at(
            'P001', 100, $this->coin, 150, $this->coin, 200,
            180, 10, Measure::at('UNIT'), 'SER001', '10x10x10',
            5, SubBrand::at('ACME_ECO', Brand::at('ACME')), Supplier::at('Supplier01')
        );
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

    public function test_can_create_valid_stock_buy()
    {
        $stockBuy = StockBuy::at($this->product, 10, '2025-11-05', 'Initial purchase');

        $this->assertEquals($this->product->id, $stockBuy->product_id);
        $this->assertEquals(10, $stockBuy->amount);
        $this->assertEquals('Initial purchase', $stockBuy->description);
        $this->assertEquals('2025-11-05', $stockBuy->date);
    }

    public function test_cannot_create_stock_buy_with_invalid_product()
    {
        $this->shouldThrowAndAssert(
            fn() => StockBuy::at(null, 10, '2025-11-05', 'Invalid purchase'),
            \TypeError::class,
            fn($e) => $this->assertStringContainsString('must be of type', $e->getMessage())
        );
    }

    public function test_supplier_cost_must_be_non_negative_number()
    {
        $this->shouldThrowAndAssert(
            fn() => StockBuy::at($this->product, -5, '2025-11-05', 'Invalid purchase'),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(StockBuy::$invalidAmount, $e->getMessage())
        );
    }

    public function test_cannot_create_stock_buy_with_invalid_date()
    {
        $this->shouldThrowAndAssert(
            fn() => StockBuy::at($this->product, 10, '2025-15-05', 'Purchase'),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(StockBuy::$invalidDate, $e->getMessage())
        );
    }

    public function test_cannot_create_stock_buy_with_description_too_long()
    {
        $longDescription = str_repeat('A', 256);

        $this->shouldThrowAndAssert(
            fn() => StockBuy::at($this->product, 10, '2025-11-05', $longDescription),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(StockBuy::$descriptionTooLong, $e->getMessage())
        );
    }
}
