<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum MaintenanceCategory: string implements HasLabel
{
    case Ac = 'ac';
    case Electricity = 'electricity';
    case Water = 'water';
    case Wifi = 'wifi';
    case Cleaning = 'cleaning';
    case Facility = 'facility';
    case Security = 'security';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Ac => 'AC',
            self::Electricity => 'Listrik',
            self::Water => 'Air',
            self::Wifi => 'WiFi / Internet',
            self::Cleaning => 'Kebersihan',
            self::Facility => 'Fasilitas',
            self::Security => 'Keamanan',
            self::Other => 'Lainnya',
        };
    }

    public function color(): string
    {
        return 'neutral';
    }
}
