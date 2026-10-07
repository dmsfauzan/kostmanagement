<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum InvoiceStatus: string implements HasLabel
{
    case Draft = 'draft';
    case Issued = 'issued';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Issued => 'Terbit',
            self::PartiallyPaid => 'Dibayar Sebagian',
            self::Paid => 'Lunas',
            self::Overdue => 'Jatuh Tempo',
            self::Void => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Issued => 'info',
            self::PartiallyPaid => 'warning',
            self::Paid => 'success',
            self::Overdue => 'danger',
            self::Void => 'neutral',
        };
    }

    public function isPayable(): bool
    {
        return in_array($this, [self::Issued, self::PartiallyPaid, self::Overdue], true);
    }
}
