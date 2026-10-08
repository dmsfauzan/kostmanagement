<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum TenantDocumentType: string implements HasLabel
{
    case Ktp = 'ktp';
    case Contract = 'contract';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Ktp => 'KTP',
            self::Contract => 'Kontrak',
            self::Other => 'Lainnya',
        };
    }

    public function color(): string
    {
        return 'neutral';
    }
}
