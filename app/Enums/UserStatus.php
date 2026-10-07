<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum UserStatus: string implements HasLabel
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Inactive => 'Nonaktif',
            self::Suspended => 'Ditangguhkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'neutral',
            self::Suspended => 'danger',
        };
    }
}
