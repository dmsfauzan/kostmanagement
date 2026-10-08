<x-layouts.tenant :title="$announcement->title">
    <x-ui.card>
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-base font-semibold text-gray-900 dark:text-white">{{ $announcement->title }}</p>
                <p class="mt-0.5 text-xs text-gray-500">{{ $announcement->publish_at?->translatedFormat('d M Y H:i') ?? $announcement->created_at->translatedFormat('d M Y') }}</p>
            </div>
            <x-ui.status-badge :status="$announcement->status" />
        </div>

        <div class="mt-4 whitespace-pre-line text-sm leading-6 text-gray-700 dark:text-gray-200">{{ $announcement->body }}</div>

        @if ($announcement->attachment_path)
            <div class="mt-4">
                <a href="{{ route('tenant.announcements.attachment', $announcement) }}" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-primary-600 ring-1 ring-inset ring-primary-200 hover:bg-primary-50 dark:text-primary-400 dark:ring-primary-800">
                    <x-ui.icon name="download" class="size-4" /> Unduh Lampiran
                </a>
            </div>
        @endif
    </x-ui.card>
</x-layouts.tenant>
