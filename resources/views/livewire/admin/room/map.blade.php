<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <x-ui.page-header title="Peta Kamar" description="Lihat hunian kamar secara visual per gedung dan lantai." />
        <select wire:model.live="property" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Semua properti</option>
            @foreach ($properties as $id => $name)
                <option value="{{ $id }}">{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach (['total' => 'Total Kamar', 'occupied' => 'Terisi', 'available' => 'Tersedia', 'maintenance' => 'Perbaikan'] as $key => $label)
            <x-ui.stat-card :label="$label" :value="$occupancy[$key]" />
        @endforeach
    </div>

    @if ($buildings->isEmpty())
        <x-ui.empty-state title="Belum ada kamar" description="Tambahkan kamar untuk menampilkan peta hunian." />
    @else
        <div class="space-y-8">
            @foreach ($buildings as $name => $floors)
                <div>
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                        {{ $name }}
                        <span class="ml-2 text-xs font-normal text-gray-400">{{ $floors->flatten()->count() }} kamar</span>
                    </h3>

                    @foreach ($floors as $floorName => $rooms)
                        <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $floorName }}</p>

                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                                @foreach ($rooms as $room)
                                    @php
                                        $styles = [
                                            'available' => 'bg-success-50 border-success-200 text-success-700 dark:bg-success-950 dark:border-success-800 dark:text-success-300',
                                            'occupied' => 'bg-primary-50 border-primary-200 text-primary-700 dark:bg-primary-950 dark:border-primary-800 dark:text-primary-300',
                                            'reserved' => 'bg-info-50 border-info-200 text-info-700 dark:bg-info-950 dark:border-info-800 dark:text-info-300',
                                            'maintenance' => 'bg-warning-50 border-warning-200 text-warning-700 dark:bg-warning-950 dark:border-warning-800 dark:text-warning-300',
                                            'inactive' => 'bg-gray-50 border-gray-200 text-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400',
                                        ][$room->status->value] ?? 'bg-gray-50 border-gray-200 text-gray-500';
                                    @endphp

                                    <a href="{{ route('admin.rooms.edit', $room) }}" wire:navigate class="rounded-lg border p-3 text-center transition hover:shadow-sm {{ $styles }}">
                                        <p class="text-sm font-bold">{{ $room->number }}</p>
                                        <p class="mt-1 text-xs font-medium">{{ $room->roomType?->name ?? 'Standard' }}</p>
                                        <p class="mt-1 text-xs">{{ \App\Support\Money::format($room->price) }}</p>
                                        <x-ui.badge :variant="$room->status->color()" class="mt-2">{{ $room->status->label() }}</x-ui.badge>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif
</div>
