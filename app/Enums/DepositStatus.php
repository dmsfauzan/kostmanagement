<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum DepositStatus: string implements HasLabel
{
    case Held = 'held';
    case PartiallyReturned = 'partially_returned';
    case Returned = 'returned';
    case Forfeited = 'forfeited';

    public function label(): string
    {
        return match ($this) {
            self::Held => 'Ditahan',
            self::PartiallyReturned => 'Dikembalikan Sebagian',
            self::Returned => 'Dikembalikan',
            self::Forfeited => 'Hangus',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Held => 'info',
            self::PartiallyReturned => 'warning',
            self::Returned => 'success',
            self::Forfeited => 'danger',
        };
    }
}
