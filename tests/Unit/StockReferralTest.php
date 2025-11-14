<?php

namespace Tests\Unit;

use App\Modules\Brand\Domain\Brand;
use App\Modules\Coin\Domain\Coin;
use App\Modules\Measure\Domain\Measure;
use App\Modules\Product\Domain\Product;
use App\Modules\StockReferral\Domain\StockReferral;
use App\Modules\SubBrand\Domain\SubBrand;
use App\Modules\Supplier\Domain\Supplier;
use PHPUnit\Framework\TestCase;

class StockReferralTest extends TestCase
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

    public function test_can_create_valid_stock_referral()
    {
        $stockReferral = StockReferral::at($this->product, 5, '2025-11-05');

        $this->assertEquals($this->product->id, $stockReferral->product_id);
        $this->assertEquals(5, $stockReferral->amount);
        $this->assertEquals('2025-11-05', $stockReferral->date);
    }

    public function test_cannot_create_stock_referral_with_invalid_product()
    {
        $this->shouldThrowAndAssert(
            fn() => StockReferral::at(null, 5, '2025-11-05'),
            \TypeError::class,
            fn($e) => $this->assertStringContainsString('must be of type', $e->getMessage())
        );
    }

    public function test_cannot_create_stock_referral_with_negative_amount()
    {
        $this->shouldThrowAndAssert(
            fn() => StockReferral::at($this->product, -5, '2025-11-05'),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(StockReferral::$invalidAmount, $e->getMessage())
        );
    }

    public function test_cannot_create_stock_referral_with_invalid_date()
    {
        $this->shouldThrowAndAssert(
            fn() => StockReferral::at($this->product, 5, '2025-15-05'),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(StockReferral::$invalidDate, $e->getMessage())
        );
    }
}
