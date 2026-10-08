<?php

namespace App\Livewire\Admin\PaymentMethod;

use App\Enums\PaymentMethodType;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    public string $name = '';

    public string $type = 'transfer';

    public ?string $account_name = '';

    public ?string $account_number = '';

    public ?string $instructions = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        Gate::authorize('payment.create');

        $data = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', 'string'],
            'account_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'instructions' => ['nullable', 'string'],
        ]);

        PaymentMethod::query()->create([
            'name' => $data['name'],
            'code' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'type' => $data['type'],
            'account_name' => $data['account_name'] ?: null,
            'account_number' => $data['account_number'] ?: null,
            'instructions' => $data['instructions'] ?: null,
            'is_active' => true,
            'sort_order' => (int) PaymentMethod::query()->max('sort_order') + 1,
        ]);

        $this->reset(['name', 'type', 'account_name', 'account_number', 'instructions']);
        $this->type = 'transfer';
        session()->flash('status', 'Metode pembayaran ditambahkan.');
    }

    public function toggleActive(int $id): void
    {
        Gate::authorize('payment.create');

        $method = PaymentMethod::query()->findOrFail($id);
        $method->update(['is_active' => ! $method->is_active]);
        session()->flash('status', 'Status metode diperbarui.');
    }

    public function delete(int $id): void
    {
        Gate::authorize('payment.create');

        PaymentMethod::query()->findOrFail($id)->delete();
        session()->flash('status', 'Metode pembayaran dihapus.');
    }

    public function render()
    {
        Gate::authorize('payment.view');

        $methods = PaymentMethod::query()
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy('sort_order')
            ->paginate(12);

        return view('livewire.admin.payment-method.index', [
            'methods' => $methods,
            'types' => PaymentMethodType::cases(),
        ])->layout('components.layouts.admin', ['title' => 'Metode Pembayaran']);
    }
}
