<div class="space-y-6">
    <x-ui.page-header title="Pengeluaran" description="Catat biaya operasional kost.">
        <x-slot:actions>
            @can('expense.create')
                <x-ui.button :href="route('admin.expenses.create')" wire:navigate>
                    <x-ui.icon name="plus" class="size-4" /> Tambah Pengeluaran
                </x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2">
        <x-ui.stat-card label="Total (filter berlaku)" :value="\App\Support\Money::format($total)" color="danger">
            <x-slot:icon><x-ui.icon name="receipt-percent" class="size-5" /></x-slot:icon>
        </x-ui.stat-card>
        <x-ui.card>
            <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Kelola Kategori</h3>
            <form wire:submit="addCategory" class="flex gap-2">
                <x-ui.input name="newCategory" wire:model="newCategory" placeholder="Nama kategori" />
                <x-ui.button type="submit" size="md">Tambah</x-ui.button>
            </form>
        </x-ui.card>
    </div>

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center dark:border-gray-700">
            <div class="sm:w-64"><x-ui.search-input model="search" placeholder="Cari vendor / deskripsi..." /></div>
            <select wire:model.live="category" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua kategori</option>
                @foreach ($categories as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            <select wire:model.live="property" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua properti</option>
                @foreach ($properties as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="from" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <input type="date" wire:model.live="to" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
        </div>

        @if ($expenses->isEmpty())
            <div class="p-6"><x-ui.empty-state title="Belum ada pengeluaran" description="Catat pengeluaran operasional di sini." /></div>
        @else
            <x-ui.table :headings="['Tanggal', 'Deskripsi', 'Kategori', 'Nominal', '']">
                @foreach ($expenses as $expense)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $expense->expense_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $expense->vendor ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($expense->description, 60) }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $expense->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium text-danger-600">{{ \App\Support\Money::format($expense->amount) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @can('expense.update')
                                    <x-ui.button :href="route('admin.expenses.edit', $expense)" variant="secondary" size="sm" wire:navigate>Ubah</x-ui.button>
                                @endcan
                                @can('expense.delete')
                                    <x-ui.confirm-dialog title="Hapus pengeluaran" :message="'Hapus pengeluaran '.\App\Support\Money::format($expense->amount).'?' " :action="'delete('.$expense->id.')'">
                                        <x-slot:trigger>
                                            <button type="button" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-950">Hapus</button>
                                        </x-slot:trigger>
                                    </x-ui.confirm-dialog>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $expenses->links() }}</div>
        @endif
    </x-ui.card>
</div>
