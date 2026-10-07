<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum LateFeeType: string implements HasLabel
{
    case None = 'none';
    case FixedDaily = 'fixed_daily';
    case Percentage = 'percentage';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Tanpa Denda',
            self::FixedDaily => 'Denda Tetap / Hari',
            self::Percentage => 'Persentase',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::None => 'neutral',
            self::FixedDaily => 'warning',
            self::Percentage => 'warning',
        };
    }
}
