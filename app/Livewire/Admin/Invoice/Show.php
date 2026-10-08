<?php

namespace App\Livewire\Admin\Invoice;

use App\Enums\InvoiceItemType;
use App\Models\Invoice;
use App\Services\BillingService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Show extends Component
{
    public Invoice $invoice;

    public string $voidReason = '';

    /** @var array{item: string, description: string, quantity: int, unit_price: int} */
    public array $newItem = [
        'item' => 'other',
        'description' => '',
        'quantity' => 1,
        'unit_price' => 0,
    ];

    public function mount(Invoice $invoice): void
    {
        Gate::authorize('invoice.view');
        $this->invoice = $invoice;
    }

    public function issue(): void
    {
        Gate::authorize('invoice.update');

        try {
            app(BillingService::class)->issue($this->invoice->refresh());
            session()->flash('status', 'Tagihan berhasil diterbitkan.');
        } catch (\InvalidArgumentException $thrown) {
            session()->flash('error', $thrown->getMessage());
        }
    }

    public function void(): void
    {
        Gate::authorize('invoice.void');

        $this->validate([
            'voidReason' => ['required', 'string', 'min:5', 'max:255'],
        ]);

        try {
            app(BillingService::class)->void($this->invoice->refresh(), $this->voidReason);
            session()->flash('status', 'Tagihan berhasil dibatalkan.');
        } catch (\InvalidArgumentException $thrown) {
            session()->flash('error', $thrown->getMessage());
        }
    }

    public function addItem(): void
    {
        Gate::authorize('invoice.update');

        $this->validate([
            'newItem.item' => ['required', 'string'],
            'newItem.description' => ['required', 'string', 'max:200'],
            'newItem.quantity' => ['required', 'integer', 'min:1'],
            'newItem.unit_price' => ['required', 'integer', 'min:0'],
        ]);

        app(BillingService::class)->addItem($this->invoice->refresh(), [
            'type' => InvoiceItemType::from($this->newItem['item']),
            'description' => $this->newItem['description'],
            'quantity' => $this->newItem['quantity'],
            'unit_price' => $this->newItem['unit_price'],
        ]);

        $this->reset('newItem');
        session()->flash('status', 'Item berhasil ditambahkan.');
    }

    public function removeItem(int $itemId): void
    {
        Gate::authorize('invoice.update');

        try {
            app(BillingService::class)->removeItem($this->invoice->refresh(), $itemId);
            session()->flash('status', 'Item berhasil dihapus.');
        } catch (\Exception) {
            session()->flash('error', 'Item tidak ditemukan.');
        }
    }

    public function render()
    {
        $invoice = $this->invoice->load(['tenant', 'lease.room', 'items', 'payments.method']);

        return view('livewire.admin.invoice.show', [
            'invoice' => $invoice,
            'types' => InvoiceItemType::cases(),
        ])->layout('components.layouts.admin', ['title' => $invoice->invoice_number]);
    }
}
