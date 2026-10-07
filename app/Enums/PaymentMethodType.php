<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum PaymentMethodType: string implements HasLabel
{
    case Cash = 'cash';
    case Transfer = 'transfer';
    case EWallet = 'ewallet';
    case Qris = 'qris';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Transfer => 'Transfer Bank',
            self::EWallet => 'E-Wallet',
            self::Qris => 'QRIS',
        };
    }

    public function color(): string
    {
        return 'neutral';
    }
}
