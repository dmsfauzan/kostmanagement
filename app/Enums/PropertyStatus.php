<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum PropertyStatus: string implements HasLabel
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Inactive => 'Nonaktif',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'neutral',
        };
    }
}
