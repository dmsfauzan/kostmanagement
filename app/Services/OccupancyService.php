<?php

namespace App\Services;

use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Support\Facades\Cache;

class OccupancyService
{
    private const VERSION_KEY = 'occupancy.cache.version';

    /**
     * Cached occupancy summary for a property (or the whole portfolio when null).
     *
     * @return array{
     *     total: int,
     *     occupied: int,
     *     available: int,
     *     maintenance: int,
     *     reserved: int,
     *     inactive: int,
     *     occupancy_rate: float,
     *     by_status: array<string, int>
     * }
     */
    public function summary(?int $propertyId = null): array
    {
        $key = 'occupancy.'.self::version().'.'.($propertyId ?? 'all');

        return Cache::remember($key, 60, fn (): array => $this->compute($propertyId));
    }

    public function flush(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }

    private static function version(): int
    {
        return (int) Cache::rememberForever(self::VERSION_KEY, fn (): int => 1);
    }

    /**
     * @return array{
     *     total: int,
     *     occupied: int,
     *     available: int,
     *     maintenance: int,
     *     reserved: int,
     *     inactive: int,
     *     occupancy_rate: float,
     *     by_status: array<string, int>
     * }
     */
    private function compute(?int $propertyId): array
    {
        $counts = Room::query()
            ->when($propertyId, fn ($query) => $query->where('property_id', $propertyId))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $byStatus = collect(RoomStatus::cases())
            ->mapWithKeys(fn (RoomStatus $status): array => [
                $status->value => (int) ($counts[$status->value] ?? 0),
            ])
            ->all();

        $total = array_sum($byStatus);
        $occupied = $byStatus[RoomStatus::Occupied->value];

        return [
            'total' => $total,
            'occupied' => $occupied,
            'available' => $byStatus[RoomStatus::Available->value],
            'maintenance' => $byStatus[RoomStatus::Maintenance->value],
            'reserved' => $byStatus[RoomStatus::Reserved->value],
            'inactive' => $byStatus[RoomStatus::Inactive->value],
            'occupancy_rate' => $total > 0 ? round($occupied / $total * 100, 1) : 0.0,
            'by_status' => $byStatus,
        ];
    }
}
