<?php

namespace App\Repositories;

use App\Enums\SpotType;
use App\Models\ParkingSpot;
use Illuminate\Database\Eloquent\Collection;

class ParkingSpotRepository
{
    public function findById(int $id): ParkingSpot
    {
        return ParkingSpot::findOrFail($id);
    }


    public function lockForUpdate(int $id): ParkingSpot
    {
        return ParkingSpot::where('id', $id)->lockForUpdate()->firstOrFail();
    }


    public function findConsecutiveRegularSpots(int $startId, int $count = 3): Collection
    {
        $anchor = ParkingSpot::findOrFail($startId);

        if ($anchor->type !== SpotType::Regular) {
            return new Collection();
        }

        $spots = ParkingSpot::where('parking_section_id', $anchor->parking_section_id)
            ->where('is_occupied', false)
            ->whereBetween('number', [$anchor->number, $anchor->number + $count - 1])
            ->orderBy('number')
            ->lockForUpdate()
            ->get();

        if ($spots->count() !== $count) {
            return new Collection();
        }

        return $spots;
    }

    public function markOccupied(Collection $spots): void
    {
        ParkingSpot::whereIn('id', $spots->pluck('id'))
            ->update(['is_occupied' => true]);
    }

    public function markAvailable(Collection $spots): void
    {
        ParkingSpot::whereIn('id', $spots->pluck('id'))
            ->update(['is_occupied' => false]);
    }

    /**
     * Aggregate totals per spot type for a given lot — single query with join, no N+1.
     *
     * @return array<string, array{total: int, available: int}>
     */
    public function aggregateByType(int $lotId): array
    {
        $rows = ParkingSpot::query()
            ->join('parking_sections', 'parking_sections.id', '=', 'parking_spots.parking_section_id')
            ->where('parking_sections.parking_lot_id', $lotId)
            ->selectRaw('parking_spots.type, COUNT(*) as total, SUM(CASE WHEN parking_spots.is_occupied = false THEN 1 ELSE 0 END) as available')
            ->groupBy('parking_spots.type')
            ->get();

        $result = [];
        foreach ($rows as $row) {
            $result[$row->type->value] = [
                'total' => (int) $row->total,
                'available' => (int) $row->available,
            ];
        }

        return $result;
    }
}
