<div class="mx-auto max-w-3xl space-y-6">
    <x-ui.page-header :title="$expense ? 'Ubah Pengeluaran' : 'Tambah Pengeluaran'" description="Catat detail biaya operasional.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.expenses')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <form wire:submit="save" class="space-y-6">
        <x-ui.card>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select name="property_id" label="Properti" wire:model="property_id" :required="true" :options="$properties" :placeholder="null" class="sm:col-span-2" />
                <div class="space-y-1.5" x-data="{ open: false }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            @focus="open = true"
                            placeholder="Ketik untuk mencari kategori..."
                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        >
                        <div x-show="open" @click.outside="open = false" class="absolute z-10 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800">
                            @php $matching = \App\Models\ExpenseCategory::query()->orderBy('name')->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))->limit(20)->get(); @endphp
                            @forelse ($matching as $category)
                                <button type="button" wire:click="setCategory({{ $category->id }})" @click="open = false" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-700 {{ $expense_category_id == $category->id ? 'font-semibold text-primary-600' : 'text-gray-700 dark:text-gray-200' }}">
                                    {{ $category->name }}
                                </button>
                            @empty
                                <p class="px-3 py-2 text-sm text-gray-400">Tidak ada kategori.</p>
                            @endforelse
                        </div>
                    </div>
                    @php $selected = \App\Models\ExpenseCategory::query()->find($expense_category_id); @endphp
                    @if ($selected)
                        <p class="text-xs text-gray-500">Terpilih: <span class="font-medium">{{ $selected->name }}</span></p>
                    @endif
                    @error('expense_category_id')<p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>@enderror
                </div>
                <x-ui.money-input model="amount" label="Nominal" :required="true" />
                <x-ui.input name="expense_date" label="Tanggal" type="date" wire:model="expense_date" :required="true" />
                <x-ui.input name="vendor" label="Vendor / Penerima" wire:model="vendor" />
                <x-ui.textarea name="description" label="Deskripsi" wire:model="description" class="sm:col-span-2" />
                <div class="sm:col-span-2">
                    <x-ui.file-upload model="receipt" label="Struk / Bukti (opsional)" accept="image/*,.pdf" hint="JPG, PNG, WebP, atau PDF hingga 4 MB" />
                    @if ($expense?->receipt_path)
                        <a href="{{ route('admin.expenses.receipt', $expense) }}" class="mt-2 inline-block text-sm font-medium text-primary-600">Unduh struk saat ini</a>
                    @endif
                </div>
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.button :href="route('admin.expenses')" variant="secondary" wire:navigate>Batal</x-ui.button>
            <x-ui.button type="submit">Simpan</x-ui.button>
        </div>
    </form>
</div>
