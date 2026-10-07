<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum BillingCycle: string implements HasLabel
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case SemiAnnual = 'semi_annual';
    case Annual = 'annual';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Bulanan',
            self::Quarterly => 'Triwulan',
            self::SemiAnnual => 'Semesteran',
            self::Annual => 'Tahunan',
            self::Custom => 'Kustom',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Monthly => 'info',
            self::Quarterly => 'primary',
            self::SemiAnnual => 'warning',
            self::Annual => 'success',
            self::Custom => 'neutral',
        };
    }
}
