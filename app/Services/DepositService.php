<?php

namespace App\Services;

use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\Lease;
use InvalidArgumentException;

class DepositService
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * Hold the deposit when a lease becomes active.
     */
    public function hold(Lease $lease): Deposit
    {
        $deposit = Deposit::query()->updateOrCreate(
            ['lease_id' => $lease->id],
            [
                'tenant_id' => $lease->tenant_id,
                'amount' => $lease->deposit_amount,
                'status' => DepositStatus::Held,
                'held_at' => now(),
            ],
        );

        $this->audit->record('deposit.held', $deposit, [], [
            'amount' => $deposit->amount,
            'status' => $deposit->status->value,
        ], 'Deposit');

        return $deposit;
    }

    /**
     * Settle a deposit at move-out.
     */
    public function settle(
        Lease $lease,
        int $deduction,
        int $refund,
        ?string $notes = null,
    ): Deposit {
        $deposit = $lease->deposit()->firstOrFail();

        if ($deduction < 0 || $refund < 0) {
            throw new InvalidArgumentException('Nominal potongan atau pengembalian tidak boleh negatif.');
        }

        if ($deduction + $refund > $deposit->amount) {
            throw new InvalidArgumentException('Total potongan dan pengembalian melebihi nominal deposit.');
        }

        $status = match (true) {
            $deduction >= $deposit->amount => DepositStatus::Forfeited,
            $refund >= $deposit->amount => DepositStatus::Returned,
            $deduction > 0 && $refund > 0 => DepositStatus::PartiallyReturned,
            $deduction > 0 => DepositStatus::PartiallyReturned,
            default => DepositStatus::Returned,
        };

        $before = $deposit->only(['status', 'deduction_amount', 'refund_amount']);

        $deposit->update([
            'deduction_amount' => $deduction,
            'refund_amount' => $refund,
            'status' => $status,
            'returned_at' => in_array($status, [DepositStatus::Returned, DepositStatus::PartiallyReturned], true) ? now() : null,
            'notes' => $notes,
        ]);

        $this->audit->record('deposit.settled', $deposit, $before, $deposit->only([
            'status', 'deduction_amount', 'refund_amount',
        ]), 'Deposit');

        return $deposit;
    }
}
