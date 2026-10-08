<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceNumberGenerator
{
    public function __construct(private readonly SettingsService $settings) {}

    public function generate(): string
    {
        $prefix = (string) $this->settings->get('billing.prefix', 'INV');
        $prefix = $prefix !== '' ? $prefix : 'INV';
        $year = now()->year;

        $sequence = Invoice::query()->whereYear('created_at', $year)->count() + 1;

        do {
            $number = sprintf('%s-%d-%04d', $prefix, $year, $sequence);
            $sequence++;
        } while (Invoice::query()->where('invoice_number', $number)->exists());

        return $number;
    }
}
