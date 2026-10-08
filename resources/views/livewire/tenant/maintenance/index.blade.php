<div class="space-y-4">
    <x-ui.page-header title="Keluhan Saya" description="Laporkan dan pantau keluhan kamar Anda." />

    <x-ui.button :href="route('tenant.maintenance.create')" class="w-full" wire:navigate>
        <x-ui.icon name="plus" class="size-4" /> Buat Keluhan Baru
    </x-ui.button>

    @if ($tickets->isEmpty())
        <x-ui.empty-state title="Belum ada keluhan" description="Keluhan yang Anda buat akan tampil di sini." />
    @else
        <div class="space-y-3">
            @foreach ($tickets as $ticket)
                <a href="{{ route('tenant.maintenance.show', $ticket) }}" wire:navigate class="block rounded-xl border border-gray-200 bg-white p-4 transition hover:shadow dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $ticket->title }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $ticket->ticket_number }} · {{ $ticket->category->label() }} · {{ $ticket->created_at->translatedFormat('d M Y') }}</p>
                        </div>
                        <x-ui.status-badge :status="$ticket->status" />
                    </div>
                    <div class="mt-3 flex items-center gap-2 border-t border-gray-100 pt-3 dark:border-gray-700">
                        <x-ui.badge :variant="$ticket->priority->color()">{{ $ticket->priority->label() }}</x-ui.badge>
                        @if ($ticket->room)
                            <span class="text-xs text-gray-500">Kamar {{ $ticket->room->number }}</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div>{{ $tickets->links() }}</div>
    @endif
</div>
