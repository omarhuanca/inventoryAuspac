<?php

namespace Tests\Unit;

use App\Modules\Measure\Domain\Measure;
use PHPUnit\Framework\TestCase;

class MeasureTest extends TestCase
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

    public function test_can_create_measure_code()
    {
        $measure = Measure::at('UNIT');
        $this->assertEquals('UNIT', $measure->code);
    }

    public function test_code_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            fn() => Measure::at(''),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Measure::$codeEmpty, $e->getMessage())
        );
    }

    public function test_code_too_long_is_invalid()
    {
        $this->shouldThrowAndAssert(
            fn() => Measure::at(str_repeat('L', 11)),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Measure::$codeLength, $e->getMessage())
        );
    }

    public function test_code_with_invalid_characters()
    {
        $this->shouldThrowAndAssert(
            fn() => Measure::at('UNIT@'),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Measure::$codeInvalidChars, $e->getMessage())
        );
    }
}
