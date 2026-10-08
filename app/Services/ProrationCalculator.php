<?php

namespace App\Services;

use Carbon\CarbonInterface;

class ProrationCalculator
{
    public function __construct(private readonly SettingsService $settings) {}

    /**
     * Calculate rent for a billing period, prorating when the tenant moves in
     * after the period start and proration is enabled.
     */
    public function rent(
        int $monthlyRent,
        CarbonInterface $periodStart,
        CarbonInterface $periodEnd,
        ?CarbonInterface $moveIn = null,
    ): int {
        if (! $this->settings->get('billing.prorate_enabled', false)) {
            return $monthlyRent;
        }

        if ($moveIn === null || $moveIn->lessThanOrEqualTo($periodStart)) {
            return $monthlyRent;
        }

        if ($moveIn->greaterThan($periodEnd)) {
            return 0;
        }

        $daysInPeriod = $periodStart->diffInDays($periodEnd) + 1;
        $activeDays = $moveIn->diffInDays($periodEnd) + 1;

        $basis = (int) $this->settings->get('billing.prorate_basis_days', 0);
        $divisor = $basis > 0 ? $basis : $daysInPeriod;

        $amount = (int) round($monthlyRent * $activeDays / $divisor);

        return min($amount, $monthlyRent);
    }
}
