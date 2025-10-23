<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\SubBrand;
use PHPUnit\Framework\TestCase;

class SubBrandTest extends TestCase
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

    public function test_can_create_subbrand()
    {
        $brand = Brand::at('ACME_CO');
        $subBrand = SubBrand::at('ACME_CO_ECO', $brand);

        $this->assertEquals('ACME_CO_ECO', $subBrand->code);
        $this->assertEquals($brand->id, $subBrand->brand_id);
    }

    public function test_code_cannot_be_empty()
    {
        $brand = Brand::at('ACME_CO');

        $this->shouldThrowAndAssert(
            fn() => SubBrand::at('', $brand),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(SubBrand::$codeEmpty, $e->getMessage())
        );
    }

    public function test_code_too_short_is_invalid()
    {
        $brand = Brand::at('ACME_CO');

        $this->shouldThrowAndAssert(
            fn() => SubBrand::at('A', $brand),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(SubBrand::$codeLength, $e->getMessage())
        );
    }

    public function test_code_too_long_is_invalid()
    {
        $brand = Brand::at('ACME_CO');

        $this->shouldThrowAndAssert(
            fn() => SubBrand::at(str_repeat('A', 51), $brand),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(SubBrand::$codeLength, $e->getMessage())
        );
    }

    public function test_code_with_invalid_characters()
    {
        $brand = Brand::at('ACME_CO');

        $this->shouldThrowAndAssert(
            fn() => SubBrand::at('ACME@ECO', $brand),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(SubBrand::$codeInvalid, $e->getMessage())
        );
    }

    public function test_brand_must_be_valid_instance()
    {
        $this->shouldThrowAndAssert(
            fn() => SubBrand::at('ACME_ECO', null),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(SubBrand::$brandInvalid, $e->getMessage()
            )
        );
    }
}
