<?php

namespace App\Services;

use App\Enums\LeaseStatus;
use App\Enums\RoomStatus;
use App\Models\Lease;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeaseService
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly DepositService $deposits,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Lease
    {
        $this->assertDateRange($data['start_date'], $data['end_date']);

        $data['code'] = $this->generateCode();
        $data['created_by'] = auth()->id();
        $data['status'] = LeaseStatus::Draft;

        $lease = DB::transaction(function () use ($data): Lease {
            $lease = Lease::query()->create($data);
            $this->writeHistory($lease, null, LeaseStatus::Draft, 'Kontrak dibuat');

            return $lease;
        });

        $this->audit->record('lease.created', $lease, [], $lease->only([
            'code', 'start_date', 'end_date', 'status',
        ]), 'Lease');

        return $lease;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Lease $lease, array $data): Lease
    {
        $this->assertDateRange($data['start_date'], $data['end_date']);

        if ($lease->isActive()) {
            unset($data['room_id'], $data['tenant_id']);
        }

        $before = $lease->only(['start_date', 'end_date', 'rent_amount', 'status']);
        $lease->update($data);

        $this->audit->record('lease.updated', $lease, $before, $lease->only([
            'start_date', 'end_date', 'rent_amount', 'status',
        ]), 'Lease');

        return $lease;
    }

    public function activate(Lease $lease): Lease
    {
        if ($lease->isActive()) {
            throw new InvalidArgumentException('Kontrak sudah aktif.');
        }

        DB::transaction(function () use ($lease): void {
            $lease = Lease::query()->whereKey($lease->id)->lockForUpdate()->firstOrFail();
            $room = Room::query()->whereKey($lease->room_id)->lockForUpdate()->firstOrFail();

            if ($room->status === RoomStatus::Maintenance) {
                throw new InvalidArgumentException('Kamar sedang dalam perbaikan dan tidak dapat disewakan.');
            }

            $this->assertNoOverlap($lease);

            $from = $lease->status;
            $lease->update([
                'status' => LeaseStatus::Active,
                'signed_at' => now(),
            ]);

            $room->update(['status' => RoomStatus::Occupied]);
            $this->deposits->hold($lease);
            $this->writeHistory($lease, $from, LeaseStatus::Active, 'Kontrak diaktifkan');

            $this->audit->record('lease.activated', $lease, [
                'status' => $from->value,
            ], [
                'status' => LeaseStatus::Active->value,
            ], 'Lease');
        });

        return $lease->refresh();
    }

    public function terminate(Lease $lease, string $reason): Lease
    {
        if (! $lease->isActive() && $lease->status !== LeaseStatus::Draft) {
            throw new InvalidArgumentException('Kontrak tidak dapat dihentikan pada status saat ini.');
        }

        DB::transaction(function () use ($lease, $reason): void {
            $lease = Lease::query()->whereKey($lease->id)->lockForUpdate()->firstOrFail();
            $from = $lease->status;

            $lease->update([
                'status' => LeaseStatus::Terminated,
                'terminated_at' => now(),
                'termination_reason' => $reason,
            ]);

            $room = Room::query()->find($lease->room_id);

            if ($room && $room->status === RoomStatus::Occupied) {
                $room->update(['status' => RoomStatus::Available]);
            }

            $this->writeHistory($lease, $from, LeaseStatus::Terminated, $reason);

            $this->audit->record('lease.terminated', $lease, [
                'status' => $from->value,
            ], [
                'status' => LeaseStatus::Terminated->value,
                'reason' => $reason,
            ], 'Lease');
        });

        return $lease->refresh();
    }

    /**
     * Ensure no other open lease overlaps for the same room or tenant.
     */
    public function assertNoOverlap(Lease $lease, ?int $ignoreLeaseId = null): void
    {
        $start = $lease->start_date->toDateString();
        $end = $lease->end_date->toDateString();

        $roomConflict = Lease::query()
            ->open()
            ->overlapping($start, $end)
            ->where('room_id', $lease->room_id)
            ->when($ignoreLeaseId, fn ($query) => $query->whereKeyNot($ignoreLeaseId))
            ->exists();

        if ($roomConflict) {
            throw new InvalidArgumentException('Kamar sudah memiliki kontrak aktif pada periode tersebut.');
        }

        $tenantConflict = Lease::query()
            ->open()
            ->overlapping($start, $end)
            ->where('tenant_id', $lease->tenant_id)
            ->when($ignoreLeaseId, fn ($query) => $query->whereKeyNot($ignoreLeaseId))
            ->exists();

        if ($tenantConflict) {
            throw new InvalidArgumentException('Penghuni sudah memiliki kontrak aktif pada periode tersebut.');
        }
    }

    private function assertDateRange(mixed $start, mixed $end): void
    {
        if (strtotime((string) $start) > strtotime((string) $end)) {
            throw new InvalidArgumentException('Tanggal mulai tidak boleh setelah tanggal berakhir.');
        }
    }

    private function writeHistory(Lease $lease, ?LeaseStatus $from, LeaseStatus $to, ?string $reason = null): void
    {
        $lease->statusHistories()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'reason' => $reason,
            'actor_id' => auth()->id(),
        ]);
    }

    private function generateCode(): string
    {
        $year = now()->year;
        $sequence = Lease::query()->whereYear('created_at', $year)->count() + 1;

        do {
            $code = sprintf('LSE-%d-%04d', $year, $sequence);
            $sequence++;
        } while (Lease::query()->where('code', $code)->exists());

        return $code;
    }
}
