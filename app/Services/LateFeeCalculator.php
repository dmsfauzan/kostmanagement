<?php

namespace App\Services;

use App\Enums\LateFeeType;
use App\Models\Invoice;
use Carbon\CarbonInterface;

class LateFeeCalculator
{
    public function __construct(private readonly SettingsService $settings) {}

    /**
     * Deterministic late-fee amount for the given invoice as of a date.
     * Recalculated from scratch each run so repeated runs never accumulate.
     */
    public function calculate(Invoice $invoice, ?CarbonInterface $asOf = null): int
    {
        $asOf = $asOf ?? now();

        if (! $invoice->isPayable() || ! $invoice->due_date->isPast()) {
            return 0;
        }

        $daysLate = max(0, (int) $invoice->due_date->startOfDay()->diffInDays($asOf->copy()->startOfDay()));

        if ($daysLate <= 0) {
            return 0;
        }

        [$type, $value] = $this->resolveRule($invoice);

        return match ($type) {
            LateFeeType::FixedDaily => $value * $daysLate,
            LateFeeType::Percentage => (int) round($invoice->subtotal * $value / 10000),
            default => 0,
        };
    }

    /**
     * @return array{0: LateFeeType, 1: int}
     */
    private function resolveRule(Invoice $invoice): array
    {
        $leaseType = $invoice->lease?->late_fee_type;
        $leaseValue = (int) ($invoice->lease?->late_fee_value ?? 0);

        if ($leaseType && $leaseType !== LateFeeType::None) {
            return [$leaseType, $leaseValue];
        }

        $type = LateFeeType::tryFrom((string) $this->settings->get('billing.late_fee_type', 'none')) ?? LateFeeType::None;
        $value = (int) $this->settings->get('billing.late_fee_value', 0);

        return [$type, $value];
    }
}
