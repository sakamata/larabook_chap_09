<?php
declare(strict_types=1);

namespace App\Services;

final class CalculatorService
{
    public static function add(int $a, int $b): int
    {
        return $a + $b;
    }
}
