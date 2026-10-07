<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum LeaseStatus: string implements HasLabel
{
    case Draft = 'draft';
    case Active = 'active';
    case Expiring = 'expiring';
    case Expired = 'expired';
    case Terminated = 'terminated';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Aktif',
            self::Expiring => 'Akan Berakhir',
            self::Expired => 'Berakhir',
            self::Terminated => 'Dihentikan',
            self::Completed => 'Selesai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Active => 'success',
            self::Expiring => 'warning',
            self::Expired => 'danger',
            self::Terminated => 'danger',
            self::Completed => 'info',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Draft, self::Active, self::Expiring], true);
    }

    public function isFinished(): bool
    {
        return in_array($this, [self::Expired, self::Terminated, self::Completed], true);
    }
}
