<x-layouts.tenant title="Pengumuman">
    <div class="space-y-4">
        @if ($announcements->isEmpty())
            <x-ui.empty-state title="Belum ada pengumuman" description="Pengumuman dari pengelola akan tampil di sini." />
        @else
            <div class="space-y-3">
                @foreach ($announcements as $announcement)
                    @php
                        $recipient = $announcement->recipients->firstWhere('user_id', auth()->id());
                        $unread = $recipient && $recipient->read_at === null;
                    @endphp

                    <a href="{{ route('tenant.announcements.show', $announcement) }}" wire:navigate class="block rounded-xl border border-gray-200 bg-white p-4 transition hover:shadow dark:border-gray-700 dark:bg-gray-800 {{ $unread ? 'ring-1 ring-primary-100 dark:ring-primary-900' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $announcement->title }}</p>
                                <p class="mt-0.5 line-clamp-2 text-xs text-gray-500">{{ Str::limit($announcement->body, 80) }}</p>
                            </div>
                            @if ($unread)
                                <span class="mt-1 size-2 shrink-0 rounded-full bg-primary-500"></span>
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-gray-400">{{ $announcement->publish_at?->translatedFormat('d M Y H:i') ?? $announcement->created_at->translatedFormat('d M Y') }}</p>
                    </a>
                @endforeach
            </div>

            <div>{{ $announcements->links() }}</div>
        @endif
    </div>
</x-layouts.tenant>
