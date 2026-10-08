<x-layouts.tenant title="Notifikasi">
    <x-ui.page-header title="Notifikasi" description="Pemberitahuan terbaru untuk Anda." />

    @php
        $notifications = auth()->user()->notifications()->latest()->take(30)->get();
    @endphp

    @if ($notifications->isEmpty())
        <x-ui.empty-state title="Belum ada notifikasi" description="Pemberitahuan akan tampil di sini." />
    @else
        <div class="space-y-3">
            @foreach ($notifications as $notification)
                <x-ui.card padding="p-4">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex size-9 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950 dark:text-primary-400">
                            <x-ui.icon name="bell" class="size-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-900 dark:text-white">{{ $notification->data['message'] ?? $notification->data['full_name'] ?? 'Notifikasi' }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at->translatedFormat('d M Y H:i') }}</p>
                        </div>
                        @unless ($notification->read_at)
                            <span class="mt-1 size-2 shrink-0 rounded-full bg-primary-500"></span>
                        @endunless
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</x-layouts.tenant>
