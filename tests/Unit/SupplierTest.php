<?php

namespace Tests\Unit;

use App\Modules\Supplier\Domain\Supplier;
use PHPUnit\Framework\TestCase;

class SupplierTest extends TestCase
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

    public function test_can_create_supplier_name()
    {
        $supplier = Supplier::at('Global Supplier Co.');
        $this->assertEquals('Global Supplier Co.', $supplier->name);
    }

    public function test_name_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            fn() => Supplier::at(''),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Supplier::$nameEmpty, $e->getMessage())
        );
    }

    public function test_name_too_short_is_invalid()
    {
        $this->shouldThrowAndAssert(
            fn() => Supplier::at('A'),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Supplier::$nameLength, $e->getMessage())
        );
    }

    public function test_name_too_long_is_invalid()
    {
        $this->shouldThrowAndAssert(
            fn() => Supplier::at(str_repeat('A', 151)),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Supplier::$nameLength, $e->getMessage())
        );
    }

    public function test_name_with_invalid_characters()
    {
        $this->shouldThrowAndAssert(
            fn() => Supplier::at('Supplier@123'),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Supplier::$nameInvalid, $e->getMessage())
        );
    }
}
