<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum TenantStatus: string implements HasLabel
{
    case Prospect = 'prospect';
    case Invited = 'invited';
    case Active = 'active';
    case Inactive = 'inactive';
    case MovedOut = 'moved_out';

    public function label(): string
    {
        return match ($this) {
            self::Prospect => 'Calon',
            self::Invited => 'Diundang',
            self::Active => 'Aktif',
            self::Inactive => 'Nonaktif',
            self::MovedOut => 'Sudah Keluar',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Prospect => 'info',
            self::Invited => 'warning',
            self::Active => 'success',
            self::Inactive => 'neutral',
            self::MovedOut => 'neutral',
        };
    }
}
