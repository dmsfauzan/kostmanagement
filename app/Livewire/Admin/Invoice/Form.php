<?php

namespace App\Livewire\Admin\Invoice;

use App\Enums\InvoiceItemType;
use App\Models\Lease;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Form extends Component
{
    public ?int $lease_id = null;

    public ?string $period_start = '';

    public ?string $period_end = '';

    public ?string $notes = '';

    /** @var list<array{type: string, description: string, quantity: int, unit_price: int}> */
    public array $items = [
        ['type' => 'rent', 'description' => 'Sewa', 'quantity' => 1, 'unit_price' => 1500000],
    ];

    public function mount(): void
    {
        Gate::authorize('invoice.create');
    }

    public function addItem(): void
    {
        $this->items[] = [
            'type' => 'other',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'lease_id' => ['required', 'exists:leases,id'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'string'],
            'items.*.description' => ['required', 'string', 'max:200'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $lease = Lease::query()->findOrFail($data['lease_id']);

        $typedItems = collect($data['items'])->map(fn (array $item): array => [
            'type' => InvoiceItemType::from($item['type']),
            'description' => $item['description'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
        ])->all();

        $invoice = app(BillingService::class)->createManual(
            $lease,
            $typedItems,
            $data['period_start'] ? Carbon::parse($data['period_start']) : null,
            $data['period_end'] ? Carbon::parse($data['period_end']) : null,
            $data['notes'],
        );

        session()->flash('status', 'Tagihan '.$invoice->invoice_number.' berhasil dibuat.');

        $this->redirectRoute('admin.invoices.show', ['invoice' => $invoice->invoice_number], navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.invoice.form', [
            'leases' => Lease::query()->with('tenant')->with('room')->latest()->get(),
            'types' => InvoiceItemType::cases(),
        ])->layout('components.layouts.admin', ['title' => 'Tambah Tagihan']);
    }
}
