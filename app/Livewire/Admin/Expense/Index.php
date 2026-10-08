<?php

namespace App\Livewire\Admin\Expense;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Property;
use App\Services\AuditService;
use App\Services\FinancialService;
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

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: '')]
    public string $property = '';

    #[Url(except: '')]
    public string $from = '';

    #[Url(except: '')]
    public string $to = '';

    public string $newCategory = '';

    public function updating(string $field): void
    {
        if (in_array($field, ['search', 'category', 'property', 'from', 'to'], true)) {
            $this->resetPage();
        }
    }

    public function addCategory(): void
    {
        Gate::authorize('expense.create');

        $this->validate(['newCategory' => ['required', 'string', 'max:80']]);

        ExpenseCategory::query()->firstOrCreate(
            ['slug' => Str::slug($this->newCategory)],
            ['name' => $this->newCategory, 'is_active' => true],
        );

        $this->reset('newCategory');
        session()->flash('status', 'Kategori ditambahkan.');
    }

    public function delete(int $id): void
    {
        Gate::authorize('expense.delete');

        $expense = Expense::query()->findOrFail($id);
        app(AuditService::class)->record('expense.deleted', $expense, $expense->only(['amount', 'expense_date']), [], 'Expense');
        $expense->delete();

        app(FinancialService::class)->flush();

        session()->flash('status', 'Pengeluaran dihapus.');
    }

    public function render()
    {
        Gate::authorize('expense.view');

        $query = Expense::query()
            ->with(['category', 'property', 'creator'])
            ->when($this->search, fn ($q) => $q->where('vendor', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->where('expense_category_id', $this->category))
            ->when($this->property, fn ($q) => $q->where('property_id', $this->property))
            ->when($this->from, fn ($q) => $q->whereDate('expense_date', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('expense_date', '<=', $this->to));

        $total = (clone $query)->sum('amount');
        $expenses = $query->latest('expense_date')->paginate(12);

        return view('livewire.admin.expense.index', [
            'expenses' => $expenses,
            'total' => $total,
            'categories' => ExpenseCategory::query()->orderBy('name')->pluck('name', 'id'),
            'properties' => Property::query()->orderBy('name')->pluck('name', 'id'),
        ])->layout('components.layouts.admin', ['title' => 'Pengeluaran']);
    }
}
