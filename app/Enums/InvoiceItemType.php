<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum InvoiceItemType: string implements HasLabel
{
    case Rent = 'rent';
    case Electricity = 'electricity';
    case Water = 'water';
    case Internet = 'internet';
    case Parking = 'parking';
    case Other = 'other';
    case Discount = 'discount';
    case LateFee = 'late_fee';
    case Adjustment = 'adjustment';
    case Deposit = 'deposit';

    public function label(): string
    {
        return match ($this) {
            self::Rent => 'Sewa',
            self::Electricity => 'Listrik',
            self::Water => 'Air',
            self::Internet => 'Internet',
            self::Parking => 'Parkir',
            self::Other => 'Lainnya',
            self::Discount => 'Diskon',
            self::LateFee => 'Denda',
            self::Adjustment => 'Penyesuaian',
            self::Deposit => 'Deposit',
        };
    }

    public function color(): string
    {
        return 'neutral';
    }
}
