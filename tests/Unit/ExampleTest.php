<?php

declare(strict_types=1);

test('that true is true', function () {
    expect(true)->toBeTrue();
});

// Simple math function to test mutation testing
final class Calculator
{
    public static function add(int $a, int $b): int
    {
        return $a + $b;
    }

    public static function multiply(int $a, int $b): int
    {
        return $a * $b;
    }
}

test('calculator can add numbers', function () {
    expect(Calculator::add(2, 3))->toBe(5);
    expect(Calculator::add(0, 5))->toBe(5);
    expect(Calculator::add(-1, 1))->toBe(0);
});

test('calculator can multiply numbers', function () {
    expect(Calculator::multiply(2, 3))->toBe(6);
    expect(Calculator::multiply(0, 5))->toBe(0);
    expect(Calculator::multiply(-1, 1))->toBe(-1);
});
