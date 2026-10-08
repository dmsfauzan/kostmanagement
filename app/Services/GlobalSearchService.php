<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceTicket;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Support\Money;

class GlobalSearchService
{
    /**
     * @return array<string, array{label: string, items: list<array{label: string, subtitle: ?string, url: string}>}>
     */
    public function search(string $term, int $limit = 5): array
    {
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return [];
        }

        $like = "%{$term}%";
        $groups = [];

        $tenants = Tenant::query()
            ->where(fn ($q) => $q->where('full_name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('phone', 'like', $like))
            ->limit($limit)->get();

        if ($tenants->isNotEmpty()) {
            $groups['tenant'] = [
                'label' => 'Penghuni',
                'items' => $tenants->map(fn (Tenant $t): array => [
                    'label' => $t->full_name,
                    'subtitle' => $t->phone ?? $t->email,
                    'url' => route('admin.tenants.show', $t),
                ])->all(),
            ];
        }

        $rooms = Room::query()
            ->where('number', 'like', $like)
            ->limit($limit)->get();

        if ($rooms->isNotEmpty()) {
            $groups['room'] = [
                'label' => 'Kamar',
                'items' => $rooms->map(fn (Room $r): array => [
                    'label' => 'Kamar '.$r->number,
                    'subtitle' => $r->roomType?->name,
                    'url' => route('admin.rooms.edit', $r),
                ])->all(),
            ];
        }

        $invoices = Invoice::query()
            ->where('invoice_number', 'like', $like)
            ->limit($limit)->get();

        if ($invoices->isNotEmpty()) {
            $groups['invoice'] = [
                'label' => 'Tagihan',
                'items' => $invoices->map(fn (Invoice $i): array => [
                    'label' => $i->invoice_number,
                    'subtitle' => $i->status->label().' · '.Money::format($i->total_amount),
                    'url' => route('admin.invoices.show', $i->invoice_number),
                ])->all(),
            ];
        }

        $payments = Payment::query()
            ->where('reference', 'like', $like)
            ->limit($limit)->get();

        if ($payments->isNotEmpty()) {
            $groups['payment'] = [
                'label' => 'Pembayaran',
                'items' => $payments->map(fn (Payment $p): array => [
                    'label' => $p->reference ?? ('Pembayaran #'.$p->id),
                    'subtitle' => $p->status->label().' · '.Money::format($p->amount),
                    'url' => route('admin.payments.show', $p),
                ])->all(),
            ];
        }

        $tickets = MaintenanceTicket::query()
            ->where(fn ($q) => $q->where('ticket_number', 'like', $like)->orWhere('title', 'like', $like))
            ->limit($limit)->get();

        if ($tickets->isNotEmpty()) {
            $groups['maintenance'] = [
                'label' => 'Maintenance',
                'items' => $tickets->map(fn (MaintenanceTicket $t): array => [
                    'label' => $t->ticket_number,
                    'subtitle' => $t->title,
                    'url' => route('admin.maintenance.show', $t),
                ])->all(),
            ];
        }

        $leases = Lease::query()
            ->where('code', 'like', $like)
            ->limit($limit)->get();

        if ($leases->isNotEmpty()) {
            $groups['lease'] = [
                'label' => 'Kontrak',
                'items' => $leases->map(fn (Lease $l): array => [
                    'label' => $l->code,
                    'subtitle' => $l->tenant?->full_name,
                    'url' => route('admin.leases.show', $l),
                ])->all(),
            ];
        }

        return $groups;
    }
}
