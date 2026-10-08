<div class="space-y-6">
    <x-ui.page-header :title="$ticket->ticket_number" :description="'Kategori: '.$ticket->category->label().' · Diajukan '.$ticket->created_at->translatedFormat('d F Y H:i')">
        <x-slot:actions>
            <x-ui.button :href="route('admin.maintenance')" variant="secondary" wire:navigate>Kembali</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detail Tiket</h3></x-slot:header>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.status-badge :status="$ticket->status" />
                <x-ui.badge :variant="$ticket->priority->color()">{{ $ticket->priority->label() }}</x-ui.badge>
                @if ($ticket->isSlaOverdue())
                    <x-ui.badge variant="danger">Lewat SLA</x-ui.badge>
                @elseif ($ticket->sla_due_at)
                    <x-ui.badge variant="neutral">SLA {{ $ticket->sla_due_at->translatedFormat('d M H:i') }}</x-ui.badge>
                @endif
            </div>
            <h4 class="mt-4 text-base font-semibold text-gray-900 dark:text-white">{{ $ticket->title }}</h4>
            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600 dark:text-gray-300">{{ $ticket->description }}</p>

            <dl class="mt-4 grid gap-4 border-t border-gray-100 pt-4 sm:grid-cols-2 dark:border-gray-700">
                @foreach ([
                    'Penghuni' => $ticket->tenant?->full_name,
                    'Kamar' => $ticket->room?->number,
                    'Properti' => $ticket->property?->name,
                    'Ditugaskan' => $ticket->assignee?->name,
                    'Dibuat oleh' => $ticket->creator?->name,
                ] as $label => $value)
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $label }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $value ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-ui.card>

        <div class="space-y-6">
            <x-ui.card>
                <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Penugasan</h3></x-slot:header>
                <form wire:submit="assign" class="space-y-3">
                    <x-ui.select name="assigned_to" label="Teknisi" wire:model="assigned_to" :required="true" :options="$technicians" placeholder="Pilih teknisi..." />
                    <x-ui.button type="submit" class="w-full">Tugaskan</x-ui.button>
                </form>
            </x-ui.card>

            <x-ui.card>
                <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Ubah Status</h3></x-slot:header>
                <form wire:submit="changeStatus" class="space-y-3">
                    <x-ui.select name="status" label="Status" wire:model="status" :required="true" :options="$ticket->status->allowedTransitions() ? collect($ticket->status->allowedTransitions())->mapWithKeys(fn($s) => [$s->value => $s->label()])->all() : []" :placeholder="null" />
                    <x-ui.textarea name="note" label="Catatan" wire:model="note" rows="2" />
                    <x-ui.button type="submit" class="w-full">Perbarui</x-ui.button>
                </form>
            </x-ui.card>
        </div>
    </div>

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
                            <p class="text-xs text-gray-500">{{ $history->note }} · {{ $history->actor?->name }} · {{ $history->created_at->translatedFormat('d M Y H:i') }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="text-sm text-gray-500">Belum ada riwayat status.</p>
        @endif
    </x-ui.card>

    <x-ui.card>
        <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Diskusi</h3></x-slot:header>

        <div class="space-y-3">
            @forelse ($ticket->comments as $comment)
                <div @class(['rounded-lg p-3', 'bg-gray-50 dark:bg-gray-700/50' => ! $comment->is_internal, 'bg-warning-50 ring-1 ring-inset ring-warning-200 dark:bg-warning-950 dark:ring-warning-800' => $comment->is_internal])>
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-200">
                        {{ $comment->user?->name }} · {{ $comment->created_at->translatedFormat('d M H:i') }}
                        @if ($comment->is_internal) <span class="ml-1 rounded bg-warning-100 px-1.5 py-0.5 text-xs text-warning-700 dark:bg-warning-900 dark:text-warning-300">internal</span> @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-200">{{ $comment->body }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada diskusi.</p>
            @endforelse
        </div>

        <form wire:submit="addComment" class="mt-4 space-y-3">
            <x-ui.textarea name="comment" label="Komentar" wire:model="comment" :required="true" rows="2" />
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" wire:model="commentIsInternal" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                Hanya internal (tidak terlihat penghuni)
            </label>
            <x-ui.button type="submit">Kirim Komentar</x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card>
        <x-slot:header><h3 class="text-sm font-semibold text-gray-900 dark:text-white">Lampiran</h3></x-slot:header>

        @if ($ticket->attachments->isNotEmpty())
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($ticket->attachments as $attachment)
                    <a href="{{ route('admin.maintenance.attachment', $attachment) }}" class="block overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                        <img src="{{ route('admin.maintenance.attachment', $attachment) }}" alt="Lampiran" class="h-28 w-full object-cover">
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">Belum ada lampiran.</p>
        @endif

        <form wire:submit="addPhotos" class="mt-4 space-y-3">
            <x-ui.file-upload model="photos" label="Tambah Foto" :multiple="true" />
            <x-ui.button type="submit">Unggah</x-ui.button>
        </form>
    </x-ui.card>
</div>
