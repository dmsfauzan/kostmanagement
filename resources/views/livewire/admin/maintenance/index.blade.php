<div class="space-y-6">
    <x-ui.page-header title="Maintenance" description="Kelola keluhan dan perbaikan." />

    @php
        $openCount = $tickets->total();
    @endphp

    <x-ui.card padding="p-0">
        <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center dark:border-gray-700">
            <div class="sm:w-64"><x-ui.search-input model="search" placeholder="Cari kode / judul..." /></div>
            <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua status</option>
                @foreach ($statuses as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="priority" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua prioritas</option>
                @foreach ($priorities as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="category" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua kategori</option>
                @foreach ($categories as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
        </div>

        @if ($tickets->isEmpty())
            <div class="p-6"><x-ui.empty-state title="Belum ada keluhan" description="Keluhan penghuni akan tampil di sini." /></div>
        @else
            <x-ui.table :headings="['Tiket', 'Penghuni / Kamar', 'Prioritas', 'SLA', 'Status', '']">
                @foreach ($tickets as $ticket)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 {{ $ticket->isSlaOverdue() ? 'bg-danger-50/50 dark:bg-danger-950/30' : '' }}">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $ticket->title }}</p>
                            <p class="text-xs text-gray-500">{{ $ticket->ticket_number }} · {{ $ticket->category->label() }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                            {{ $ticket->tenant?->full_name ?? '—' }}
                            <span class="block text-xs text-gray-400">{{ $ticket->room?->number ? 'Kamar '.$ticket->room->number : '' }}</span>
                        </td>
                        <td class="px-4 py-3"><x-ui.badge :variant="$ticket->priority->color()">{{ $ticket->priority->label() }}</x-ui.badge></td>
                        <td class="px-4 py-3">
                            @if ($ticket->isSlaOverdue())
                                <x-ui.badge variant="danger">Lewat SLA</x-ui.badge>
                            @elseif ($ticket->sla_due_at)
                                <span class="text-xs text-gray-500">{{ $ticket->sla_due_at->translatedFormat('d M H:i') }}</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3"><x-ui.status-badge :status="$ticket->status" /></td>
                        <td class="px-4 py-3 text-right">
                            <x-ui.button :href="route('admin.maintenance.show', $ticket)" variant="secondary" size="sm" wire:navigate>Detail</x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $tickets->links() }}</div>
        @endif
    </x-ui.card>
</div>
