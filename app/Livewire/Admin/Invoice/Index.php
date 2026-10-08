<?php

namespace App\Livewire\Admin\Invoice;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $period = '';

    public function updating(string $field): void
    {
        if (in_array($field, ['search', 'status', 'period'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        Gate::authorize('invoice.view');

        $invoices = Invoice::query()
            ->with(['tenant', 'lease.room'])
            ->when($this->search, fn ($query) => $query->where(
                fn ($q) => $q->where('invoice_number', 'like', "%{$this->search}%")
                    ->orWhereHas('tenant', fn ($t) => $t->where('full_name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->period, fn ($query) => $query->forPeriod($this->period))
            ->latest()
            ->paginate(12);

        return view('livewire.admin.invoice.index', [
            'invoices' => $invoices,
            'statuses' => InvoiceStatus::cases(),
            'periods' => Invoice::query()
                ->whereNotNull('billing_period')
                ->distinct()
                ->orderByDesc('billing_period')
                ->pluck('billing_period'),
            'summary' => [
                'billed' => (int) Invoice::query()->where('status', '!=', InvoiceStatus::Void->value)->sum('total_amount'),
                'outstanding' => (int) Invoice::query()->unpaid()->sum('amount_due'),
                'overdue' => (int) Invoice::query()->overdue()->sum('amount_due'),
            ],
        ])->layout('components.layouts.admin', ['title' => 'Tagihan']);
    }
}
