<?php

namespace Tests\Unit;

use App\Models\Coin;
use PHPUnit\Framework\TestCase;

class CoinTest extends TestCase
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

    public function test_can_create_coin_code()
    {
        $coin = Coin::at('USD');

        $this->assertEquals('USD', $coin->code);
    }

    public function test_code_cannot_be_empty()
    {
        $this->shouldThrowAndAssert(
            fn() => Coin::at(''),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Coin::CODE_EMPTY, $e->getMessage())
        );
    }

    public function test_code_too_short_is_invalid()
    {
        $this->shouldThrowAndAssert(
            fn() => Coin::at('U'),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Coin::CODE_LENGTH, $e->getMessage())
        );
    }

    public function test_code_too_long_is_invalid()
    {
        $this->shouldThrowAndAssert(
            fn() => Coin::at(str_repeat('A', 11)),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Coin::CODE_LENGTH, $e->getMessage())
        );
    }

    public function test_code_with_invalid_characters()
    {
        $this->shouldThrowAndAssert(
            fn() => Coin::at('US$'),
            \RuntimeException::class,
            fn($e) => $this->assertEquals(Coin::CODE_INVALID_CHARS, $e->getMessage())
        );
    }
}
