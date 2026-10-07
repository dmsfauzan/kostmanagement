<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum PaymentStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Verifikasi',
            self::Verified => 'Terverifikasi',
            self::Rejected => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
            self::Refunded => 'Dikembalikan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Verified => 'success',
            self::Rejected => 'danger',
            self::Cancelled => 'neutral',
            self::Refunded => 'info',
        };
    }
}
