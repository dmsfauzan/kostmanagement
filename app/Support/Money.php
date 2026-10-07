<?php

namespace App\Support;

final class Money
{
    public const CURRENCY = 'IDR';

    public const LOCALE = 'id_ID';

    public static function format(int $amount, bool $short = false): string
    {
        if ($short) {
            return $amount >= 1000000
                ? 'Rp '.rtrim(number_format($amount / 1000000, 1, ',', '.'), ',0').'jt'
                : 'Rp '.number_format($amount, 0, ',', '.');
        }

        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    public static function parse(int|string $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        return (int) str_replace(['.', ',', 'Rp', ' '], '', (string) $value);
    }

    public static function add(int ...$values): int
    {
        return array_sum($values);
    }

    public static function subtract(int $a, int $b): int
    {
        return $a - $b;
    }
}
