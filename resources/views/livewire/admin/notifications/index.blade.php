<div class="space-y-4">
    <div class="flex items-center justify-between">
        <x-ui.page-header title="Notifikasi" description="Pemberitahuan operasional terbaru." />
        @if ($notifications->whereNull('read_at')->count() > 0)
            <x-ui.button wire:click="markAllRead" variant="secondary" size="sm">Tandai semua dibaca</x-ui.button>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <x-ui.empty-state title="Belum ada notifikasi" description="Pemberitahuan akan tampil di sini." />
    @else
        <div class="space-y-3">
            @foreach ($notifications as $notification)
                <x-ui.card padding="p-4" :class="$notification->read_at ? '' : 'ring-1 ring-primary-100 dark:ring-primary-900'">
                    <button wire:click="markRead('{{ $notification->id }}')" class="flex w-full items-start gap-3 text-left">
                        <span class="inline-flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                            <x-ui.icon name="bell" class="size-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-900 dark:text-white">{{ $notification->data['message'] ?? 'Notifikasi' }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at->translatedFormat('d M Y H:i') }}</p>
                        </div>
                        @unless ($notification->read_at)
                            <span class="mt-1 size-2 shrink-0 rounded-full bg-primary-500"></span>
                        @endunless
                    </button>
                </x-ui.card>
            @endforeach
        </div>

        <div>{{ $notifications->links() }}</div>
    @endif
</div>
