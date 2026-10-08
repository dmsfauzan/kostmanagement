<div class="space-y-4">
    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $ticket->title }}</p>
                <p class="mt-0.5 text-xs text-gray-500">{{ $ticket->ticket_number }} · {{ $ticket->category->label() }}</p>
            </div>
            <x-ui.status-badge :status="$ticket->status" />
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2">
            <x-ui.badge :variant="$ticket->priority->color()">{{ $ticket->priority->label() }}</x-ui.badge>
            @if ($ticket->room)
                <span class="text-xs text-gray-500">Kamar {{ $ticket->room->number }}</span>
            @endif
            <span class="text-xs text-gray-500">Diajukan {{ $ticket->created_at->translatedFormat('d M Y H:i') }}</span>
        </div>

        <p class="mt-4 whitespace-pre-line border-t border-gray-100 pt-4 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-200">{{ $ticket->description }}</p>

        @if ($attachments->isNotEmpty())
            <div class="mt-4 grid grid-cols-3 gap-2">
                @foreach ($attachments as $attachment)
                    <a href="{{ route('tenant.maintenance.attachment', $attachment) }}" class="block overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <img src="{{ route('tenant.maintenance.attachment', $attachment) }}" alt="Lampiran" class="h-24 w-full object-cover">
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @if ($ticket->status->value === 'resolved')
        <div class="flex gap-3">
            <x-ui.button wire:click="confirmResolved" class="flex-1">Konfirmasi Selesai</x-ui.button>
            <x-ui.button wire:click="reopen" variant="secondary" class="flex-1">Masih Bermasalah</x-ui.button>
        </div>
    @elseif ($ticket->status->value === 'closed')
        <x-ui.button wire:click="reopen" variant="secondary" class="w-full">Buka Kembali</x-ui.button>
    @endif

    <x-ui.card>
        <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Riwayat Status</h3></x-slot:header>
        @if ($ticket->statusHistories->isNotEmpty())
            <ol class="space-y-3">
                @foreach ($ticket->statusHistories as $history)
                    <li class="flex items-start gap-3 text-sm">
                        <span class="mt-1 size-2 shrink-0 rounded-full bg-primary-500"></span>
                        <div>
                            <p class="text-gray-900 dark:text-white">
                                @if ($history->from_status) {{ \Illuminate\Support\Str::headline($history->from_status) }} → @endif
                                <span class="font-semibold">{{ \Illuminate\Support\Str::headline($history->to_status) }}</span>
                            </p>
                            <p class="text-xs text-gray-500">{{ $history->note }} · {{ $history->created_at->translatedFormat('d M Y H:i') }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="text-sm text-gray-500">Belum ada riwayat.</p>
        @endif
    </x-ui.card>

    <x-ui.card>
        <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Diskusi</h3></x-slot:header>

        <div class="space-y-3">
            @forelse ($comments as $comment)
                <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-200">{{ $comment->user?->name }} · {{ $comment->created_at->translatedFormat('d M H:i') }}</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-200">{{ $comment->body }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada diskusi.</p>
            @endforelse
        </div>

        @if ($ticket->isOpen())
            <form wire:submit="addComment" class="mt-4 space-y-2">
                <x-ui.textarea name="comment" label="Balas" wire:model="comment" :required="true" rows="2" />
                <x-ui.button type="submit" class="w-full">Kirim</x-ui.button>
            </form>
        @endif
    </x-ui.card>
</div>
