<?php

namespace App\Livewire\Admin\Payment;

use App\Models\Payment;
use App\Models\PaymentMethod;
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
    public string $method = '';

    public function updating(string $field): void
    {
        if (in_array($field, ['search', 'status', 'method'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        Gate::authorize('payment.view');

        $payments = Payment::query()
            ->with(['tenant', 'invoice', 'method'])
            ->when($this->search, fn ($query) => $query->where(
                fn ($q) => $q->where('reference', 'like', "%{$this->search}%")
                    ->orWhereHas('tenant', fn ($t) => $t->where('full_name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->method, fn ($query) => $query->where('payment_method_id', $this->method))
            ->latest()
            ->paginate(12);

        return view('livewire.admin.payment.index', [
            'payments' => $payments,
            'methods' => PaymentMethod::query()->orderBy('sort_order')->pluck('name', 'id'),
            'summary' => [
                'pending' => Payment::query()->where('status', 'pending')->sum('amount'),
                'verified' => Payment::query()->where('status', 'verified')->sum('amount'),
            ],
        ])->layout('components.layouts.admin', ['title' => 'Pembayaran']);
    }
}
