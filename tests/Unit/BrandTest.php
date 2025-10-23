<?php

namespace Tests\Unit;

use App\Models\Brand;
use PHPUnit\Framework\TestCase;

class BrandTest extends TestCase
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

    public function test_can_create_brand_code()
    {
        $brand = Brand::at('ACME_CO');

        $this->assertEquals('ACME_CO', $brand->code);
    }

    public function test_code_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            fn () => Brand::at(''),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Brand::$codeEmpty, $e->getMessage())
        );
    }

    public function test_code_too_short_is_invalid()
    {
        $this->shouldThrowAndAssert(
            fn() => Brand::at('A'),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Brand::$codeLength, $e->getMessage())
        );
    }

    public function test_code_too_long_is_invalid()
    {
        $this->shouldThrowAndAssert(
            fn() => Brand::at(str_repeat('A', 51)),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Brand::$codeLength, $e->getMessage())
        );
    }

    public function test_code_with_invalid_characters()
    {
        $this->shouldThrowAndAssert(
            fn() => Brand::at('ACME@CO'),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Brand::$codeInvalidChars, $e->getMessage())
        );
    }
}
