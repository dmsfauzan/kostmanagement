<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class FinancialService
{
    private const VERSION_KEY = 'financial.cache.version';

    /**
     * Cached financial summary for the given range (defaults: current month).
     *
     * @return array{
     *     billed: int,
     *     collected: int,
     *     outstanding: int,
     *     overdue: int,
     *     expenses: int,
     *     net_cash_flow: int,
     *     monthly: list<array{month: string, billed: int, collected: int, expenses: int}>
     * }
     */
    public function summary(?int $propertyId = null, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->copy()->firstOfMonth()->startOfDay();
        $to = $to ?? now()->copy()->endOfMonth()->endOfDay();

        $key = 'financial.'.self::version().'.'.($propertyId ?? 'all').'.'.$from->toDateString().'.'.$to->toDateString();

        return Cache::remember($key, 60, fn (): array => $this->compute($propertyId, $from, $to));
    }

    /**
     * Invalidate all cached financial summaries.
     */
    public function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    private static function version(): int
    {
        return (int) Cache::rememberForever(self::VERSION_KEY, fn (): int => 1);
    }

    /**
     * @return array{
     *     billed: int,
     *     collected: int,
     *     outstanding: int,
     *     overdue: int,
     *     expenses: int,
     *     net_cash_flow: int,
     *     monthly: list<array{month: string, billed: int, collected: int, expenses: int}>
     * }
     */
    private function compute(?int $propertyId, Carbon $from, Carbon $to): array
    {
        $from = $from ?? now()->copy()->firstOfMonth()->startOfDay();
        $to = $to ?? now()->copy()->endOfMonth()->endOfDay();

        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();

        $billed = (int) Invoice::query()
            ->forProperty($propertyId)
            ->whereBetween('issue_date', [$fromDate, $toDate])
            ->where('status', '!=', InvoiceStatus::Void->value)
            ->sum('total_amount');

        $collected = (int) Payment::query()
            ->when($propertyId, fn (Builder $q) => $q->where('property_id', $propertyId))
            ->where('status', PaymentStatus::Verified->value)
            ->whereBetween('paid_at', [$fromDate, $toDate])
            ->sum('amount');

        $outstanding = (int) Invoice::query()
            ->forProperty($propertyId)
            ->whereIn('status', [
                InvoiceStatus::Issued->value,
                InvoiceStatus::PartiallyPaid->value,
                InvoiceStatus::Overdue->value,
            ])
            ->sum('amount_due');

        $overdue = (int) Invoice::query()
            ->forProperty($propertyId)
            ->overdue()
            ->sum('amount_due');

        $expenses = (int) Expense::query()
            ->forProperty($propertyId)
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->sum('amount');

        return [
            'billed' => $billed,
            'collected' => $collected,
            'outstanding' => $outstanding,
            'overdue' => $overdue,
            'expenses' => $expenses,
            'net_cash_flow' => $collected - $expenses,
            'monthly' => $this->monthlySeries($propertyId),
        ];
    }

    /**
     * @return list<array{month: string, billed: int, collected: int, expenses: int}>
     */
    private function monthlySeries(?int $propertyId): array
    {
        $series = [];

        foreach (CarbonPeriod::create(now()->copy()->subMonths(5)->firstOfMonth(), '1 month', now()->copy()->firstOfMonth()) as $month) {
            $from = $month->copy()->firstOfMonth()->toDateString();
            $to = $month->copy()->endOfMonth()->toDateString();

            $series[] = [
                'month' => $month->translatedFormat('M y'),
                'billed' => (int) Invoice::query()->forProperty($propertyId)->whereBetween('issue_date', [$from, $to])->where('status', '!=', InvoiceStatus::Void->value)->sum('total_amount'),
                'collected' => (int) Payment::query()->when($propertyId, fn (Builder $q) => $q->where('property_id', $propertyId))->where('status', PaymentStatus::Verified->value)->whereBetween('paid_at', [$from, $to])->sum('amount'),
                'expenses' => (int) Expense::query()->forProperty($propertyId)->whereBetween('expense_date', [$from, $to])->sum('amount'),
            ];
        }

        return $series;
    }
}
