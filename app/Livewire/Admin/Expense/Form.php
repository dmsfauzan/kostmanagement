<?php

namespace App\Livewire\Admin\Expense;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Property;
use App\Services\AuditService;
use App\Services\FinancialService;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public ?Expense $expense = null;

    public ?int $property_id = null;

    public ?int $expense_category_id = null;

    public ?int $amount = null;

    public string $expense_date = '';

    public ?string $vendor = '';

    public ?string $description = '';

    public $receipt;

    public bool $isSearching = false;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->isSearching = trim($this->search) !== '';
    }

    public function setCategory(int $id): void
    {
        $this->expense_category_id = $id;
        $this->isSearching = false;
        $this->search = '';
    }

    public function mount(?Expense $expense = null): void
    {
        if ($expense && $expense->exists) {
            Gate::authorize('expense.update');
            $this->expense = $expense;
            $this->property_id = $expense->property_id;
            $this->expense_category_id = $expense->expense_category_id;
            $this->amount = $expense->amount;
            $this->expense_date = $expense->expense_date->toDateString();
            $this->vendor = $expense->vendor;
            $this->description = $expense->description;
        } else {
            Gate::authorize('expense.create');
            $this->property_id = Property::query()->value('id');
            $this->expense_date = now()->toDateString();
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'property_id' => ['required', 'exists:properties,id'],
            'expense_category_id' => ['nullable', 'exists:expense_categories,id'],
            'amount' => ['required', 'integer', 'min:0'],
            'expense_date' => ['required', 'date'],
            'vendor' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $receipt = $this->receipt;
        unset($data['receipt']);
        $data['created_by'] = $this->expense?->created_by ?? auth()->id();

        $audit = app(AuditService::class);

        if ($this->expense) {
            if ($receipt) {
                $data['receipt_path'] = $receipt->store('expenses/'.$this->expense->id, config('kost.disk.private'));
            }
            $before = $this->expense->only(['amount', 'expense_date', 'vendor']);
            $this->expense->update($data);
            $audit->record('expense.updated', $this->expense, $before, $this->expense->only(['amount', 'expense_date', 'vendor']), 'Expense');
            session()->flash('status', 'Pengeluaran diperbarui.');
        } else {
            $expense = Expense::query()->create($data);
            if ($receipt) {
                $expense->update(['receipt_path' => $receipt->store('expenses/'.$expense->id, config('kost.disk.private'))]);
            }
            $audit->record('expense.created', $expense, [], $expense->only(['amount', 'expense_date', 'vendor']), 'Expense');
            session()->flash('status', 'Pengeluaran ditambahkan.');
        }

        app(FinancialService::class)->flush();

        $this->redirectRoute('admin.expenses', navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.expense.form', [
            'categories' => ExpenseCategory::query()->orderBy('name')->pluck('name', 'id'),
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
        ])->layout('components.layouts.admin', [
            'title' => $this->expense ? 'Ubah Pengeluaran' : 'Tambah Pengeluaran',
        ]);
    }
}
