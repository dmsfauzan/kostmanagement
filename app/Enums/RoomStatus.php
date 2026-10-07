<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum RoomStatus: string implements HasLabel
{
    case Available = 'available';
    case Occupied = 'occupied';
    case Reserved = 'reserved';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Occupied => 'Terisi',
            self::Reserved => 'Dipesan',
            self::Maintenance => 'Perbaikan',
            self::Inactive => 'Nonaktif',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Occupied => 'primary',
            self::Reserved => 'info',
            self::Maintenance => 'warning',
            self::Inactive => 'neutral',
        };
    }
}
