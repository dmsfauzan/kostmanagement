<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Support\Money;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class ReportService
{
    /**
     * @return array<string, string>
     */
    public function types(): array
    {
        return [
            'occupancy' => 'Okupansi Kamar',
            'revenue' => 'Pendapatan',
            'expense' => 'Pengeluaran',
            'outstanding' => 'Tagihan Belum Dibayar',
            'overdue' => 'Tagihan Jatuh Tempo',
            'tenant' => 'Penghuni',
            'lease_expiration' => 'Kontrak Akan Berakhir',
            'maintenance' => 'Maintenance',
            'payment' => 'Pembayaran',
        ];
    }

    /**
     * @param  array{property?: int|null, from?: string|null, to?: string|null}  $filters
     * @return array{title: string, headings: list<string>, rows: list<array<int, mixed>>}
     */
    public function report(string $type, array $filters = []): array
    {
        return match ($type) {
            'occupancy' => $this->occupancy($filters),
            'revenue' => $this->revenue($filters),
            'expense' => $this->expense($filters),
            'outstanding' => $this->outstanding($filters),
            'overdue' => $this->overdue($filters),
            'tenant' => $this->tenant($filters),
            'lease_expiration' => $this->leaseExpiration($filters),
            'maintenance' => $this->maintenance($filters),
            'payment' => $this->payment($filters),
            default => throw new InvalidArgumentException("Laporan '{$type}' tidak dikenal."),
        };
    }

    /**
     * @param  array{property?: int|null, from?: string|null, to?: string|null}  $filters
     * @return array{title: string, headings: list<string>, rows: list<array<int, mixed>>}
     */
    private function occupancy(array $filters): array
    {
        $rooms = Room::query()
            ->with(['property', 'building', 'floor', 'roomType'])
            ->when($filters['property'] ?? null, fn ($q, $p) => $q->where('property_id', $p))
            ->orderBy('number')
            ->get();

        return [
            'title' => 'Laporan Okupansi Kamar',
            'headings' => ['Kamar', 'Properti', 'Gedung', 'Lantai', 'Tipe', 'Harga', 'Status'],
            'rows' => $rooms->map(fn (Room $room): array => [
                $room->number,
                $room->property?->name,
                $room->building?->name,
                $room->floor?->name,
                $room->roomType?->name,
                Money::format($room->price),
                $room->status->label(),
            ])->all(),
        ];
    }

    /**
     * @param  array{property?: int|null, from?: string|null, to?: string|null}  $filters
     * @return array{title: string, headings: list<string>, rows: list<array<int, mixed>>}
     */
    private function revenue(array $filters): array
    {
        [$from, $to] = $this->range($filters);

        $invoices = Invoice::query()
            ->with(['tenant', 'lease.room'])
            ->forProperty($filters['property'] ?? null)
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->where('status', '!=', InvoiceStatus::Void->value)
            ->orderBy('issue_date')
            ->get();

        return [
            'title' => 'Laporan Pendapatan',
            'headings' => ['No. Tagihan', 'Penghuni', 'Kamar', 'Periode', 'Terbit', 'Total', 'Dibayar', 'Sisa', 'Status'],
            'rows' => $invoices->map(fn (Invoice $invoice): array => [
                $invoice->invoice_number,
                $invoice->tenant?->full_name,
                $invoice->lease?->room?->number,
                $invoice->billing_period,
                $invoice->issue_date->format('d/m/Y'),
                Money::format($invoice->total_amount),
                Money::format($invoice->amount_paid),
                Money::format($invoice->amount_due),
                $invoice->status->label(),
            ])->all(),
        ];
    }

    /**
     * @param  array{property?: int|null, from?: string|null, to?: string|null}  $filters
     * @return array{title: string, headings: list<string>, rows: list<array<int, mixed>>}
     */
    private function expense(array $filters): array
    {
        [$from, $to] = $this->range($filters);

        $expenses = Expense::query()
            ->with(['category', 'property'])
            ->forProperty($filters['property'] ?? null)
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('expense_date')
            ->get();

        return [
            'title' => 'Laporan Pengeluaran',
            'headings' => ['Tanggal', 'Kategori', 'Properti', 'Vendor', 'Deskripsi', 'Nominal'],
            'rows' => $expenses->map(fn (Expense $expense): array => [
                $expense->expense_date->format('d/m/Y'),
                $expense->category?->name,
                $expense->property?->name,
                $expense->vendor,
                $expense->description,
                Money::format($expense->amount),
            ])->all(),
        ];
    }

    /**
     * @param  array{property?: int|null}  $filters
     * @return array{title: string, headings: list<string>, rows: list<array<int, mixed>>}
     */
    private function outstanding(array $filters): array
    {
        $invoices = Invoice::query()
            ->with(['tenant', 'lease.room'])
            ->forProperty($filters['property'] ?? null)
            ->unpaid()
            ->orderBy('due_date')
            ->get();

        return [
            'title' => 'Laporan Tagihan Belum Dibayar',
            'headings' => ['No. Tagihan', 'Penghuni', 'Kamar', 'Jatuh Tempo', 'Sisa', 'Status'],
            'rows' => $invoices->map(fn (Invoice $invoice): array => [
                $invoice->invoice_number,
                $invoice->tenant?->full_name,
                $invoice->lease?->room?->number,
                $invoice->due_date->format('d/m/Y'),
                Money::format($invoice->amount_due),
                $invoice->status->label(),
            ])->all(),
        ];
    }

    /**
     * @param  array{property?: int|null}  $filters
     * @return array{title: string, headings: list<string>, rows: list<array<int, mixed>>}
     */
    private function overdue(array $filters): array
    {
        $invoices = Invoice::query()
            ->with(['tenant', 'lease.room'])
            ->forProperty($filters['property'] ?? null)
            ->overdue()
            ->orderBy('due_date')
            ->get();

        return [
            'title' => 'Laporan Tagihan Jatuh Tempo',
            'headings' => ['No. Tagihan', 'Penghuni', 'Kamar', 'Jatuh Tempo', 'Sisa', 'Hari Terlambat'],
            'rows' => $invoices->map(fn (Invoice $invoice): array => [
                $invoice->invoice_number,
                $invoice->tenant?->full_name,
                $invoice->lease?->room?->number,
                $invoice->due_date->format('d/m/Y'),
                Money::format($invoice->amount_due),
                (int) $invoice->due_date->diffInDays(now()),
            ])->all(),
        ];
    }

    /**
     * @param  array{property?: int|null}  $filters
     * @return array{title: string, headings: list<string>, rows: list<array<int, mixed>>}
     */
    private function tenant(array $filters): array
    {
        $tenants = Tenant::query()
            ->with('property')
            ->when($filters['property'] ?? null, fn ($q, $p) => $q->where('property_id', $p))
            ->orderBy('full_name')
            ->get();

        return [
            'title' => 'Laporan Penghuni',
            'headings' => ['Nama', 'Telepon', 'Email', 'Properti', 'Kendaraan', 'Status'],
            'rows' => $tenants->map(fn (Tenant $tenant): array => [
                $tenant->full_name,
                $tenant->phone,
                $tenant->email,
                $tenant->property?->name,
                trim(($tenant->vehicle_type ?? '').' '.($tenant->vehicle_number ?? '')),
                $tenant->status->label(),
            ])->all(),
        ];
    }

    /**
     * @param  array{property?: int|null}  $filters
     * @return array{title: string, headings: list<string>, rows: list<array<int, mixed>>}
     */
    private function leaseExpiration(array $filters): array
    {
        $leases = Lease::query()
            ->with(['tenant', 'room'])
            ->forProperty($filters['property'] ?? null)
            ->open()
            ->orderBy('end_date')
            ->get();

        return [
            'title' => 'Laporan Kontrak Akan Berakhir',
            'headings' => ['Kode', 'Penghuni', 'Kamar', 'Mulai', 'Berakhir', 'Sisa Hari'],
            'rows' => $leases->map(fn (Lease $lease): array => [
                $lease->code,
                $lease->tenant?->full_name,
                $lease->room?->number,
                $lease->start_date->format('d/m/Y'),
                $lease->end_date->format('d/m/Y'),
                $lease->daysRemaining(),
            ])->all(),
        ];
    }

    /**
     * @param  array{property?: int|null}  $filters
     * @return array{title: string, headings: list<string>, rows: list<array<int, mixed>>}
     */
    private function maintenance(array $filters): array
    {
        $tickets = MaintenanceTicket::query()
            ->with(['tenant', 'room', 'assignee'])
            ->forProperty($filters['property'] ?? null)
            ->orderByDesc('created_at')
            ->get();

        return [
            'title' => 'Laporan Maintenance',
            'headings' => ['No. Tiket', 'Judul', 'Penghuni', 'Kamar', 'Kategori', 'Prioritas', 'Status', 'Teknisi'],
            'rows' => $tickets->map(fn (MaintenanceTicket $ticket): array => [
                $ticket->ticket_number,
                $ticket->title,
                $ticket->tenant?->full_name,
                $ticket->room?->number,
                $ticket->category->label(),
                $ticket->priority->label(),
                $ticket->status->label(),
                $ticket->assignee?->name,
            ])->all(),
        ];
    }

    /**
     * @param  array{property?: int|null, from?: string|null, to?: string|null}  $filters
     * @return array{title: string, headings: list<string>, rows: list<array<int, mixed>>}
     */
    private function payment(array $filters): array
    {
        [$from, $to] = $this->range($filters);

        $payments = Payment::query()
            ->with(['tenant', 'invoice', 'method'])
            ->when($filters['property'] ?? null, fn ($q, $p) => $q->where('property_id', $p))
            ->whereBetween('paid_at', [$from->toDateString(), $to->toDateString()])
            ->orderBy('paid_at')
            ->get();

        return [
            'title' => 'Laporan Pembayaran',
            'headings' => ['Tanggal', 'Penghuni', 'No. Tagihan', 'Metode', 'Nominal', 'Status'],
            'rows' => $payments->map(fn (Payment $payment): array => [
                $payment->paid_at->format('d/m/Y'),
                $payment->tenant?->full_name,
                $payment->invoice?->invoice_number,
                $payment->method?->name,
                Money::format($payment->amount),
                $payment->status->label(),
            ])->all(),
        ];
    }

    /**
     * @param  array{from?: string|null, to?: string|null}  $filters
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(array $filters): array
    {
        $from = ! empty($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : now()->copy()->firstOfMonth()->startOfDay();
        $to = ! empty($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : now()->copy()->endOfMonth()->endOfDay();

        return [$from, $to];
    }
}
