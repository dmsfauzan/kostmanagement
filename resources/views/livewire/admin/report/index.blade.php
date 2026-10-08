<div class="space-y-6">
    <x-ui.page-header title="Laporan" description="Laporan operasional kost, siap diunduh.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.reports.export', ['type' => $type, 'format' => 'csv'] + array_filter(['property' => $property ?: null, 'from' => $from ?: null, 'to' => $to ?: null]))" variant="secondary">CSV</x-ui.button>
            <x-ui.button :href="route('admin.reports.export', ['type' => $type, 'format' => 'xlsx'] + array_filter(['property' => $property ?: null, 'from' => $from ?: null, 'to' => $to ?: null]))" variant="secondary">XLSX</x-ui.button>
            <x-ui.button :href="route('admin.reports.export', ['type' => $type, 'format' => 'pdf'] + array_filter(['property' => $property ?: null, 'from' => $from ?: null, 'to' => $to ?: null]))" variant="secondary">PDF</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 dark:border-gray-700">
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($types as $key => $label)
                    <button type="button" wire:click="$set('type', '{{ $key }}')" @class(['rounded-lg px-3 py-1.5 text-xs font-semibold transition', 'bg-primary-600 text-white' => $type === $key, 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300' => $type !== $key])>
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <select wire:model.live="property" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Semua properti</option>
                    @foreach ($properties as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <input type="date" wire:model.live="from" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <input type="date" wire:model.live="to" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>
        </div>

        <div class="p-4">
            <h3 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">{{ $report['title'] }} ({{ count($report['rows']) }})</h3>
            <x-ui.table :headings="$report['headings']">
                @forelse ($report['rows'] as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40">
                        @foreach ($row as $cell)
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ $cell ?? '—' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td class="px-4 py-6 text-center text-sm text-gray-400" colspan="{{ count($report['headings']) }}">Tidak ada data untuk filter ini.</td></tr>
                @endforelse
            </x-ui.table>
        </div>
    </x-ui.card>
</div>
