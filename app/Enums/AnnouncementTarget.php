<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum AnnouncementTarget: string implements HasLabel
{
    case All = 'all';
    case Property = 'property';
    case Building = 'building';
    case Floor = 'floor';
    case Room = 'room';
    case Tenant = 'tenant';

    public function label(): string
    {
        return match ($this) {
            self::All => 'Semua Penghuni',
            self::Property => 'Per Properti',
            self::Building => 'Per Gedung',
            self::Floor => 'Per Lantai',
            self::Room => 'Per Kamar',
            self::Tenant => 'Penghuni Tertentu',
        };
    }

    public function color(): string
    {
        return 'neutral';
    }
}
