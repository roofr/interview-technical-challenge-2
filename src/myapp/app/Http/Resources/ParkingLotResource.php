<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkingLotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $availabilityStats = $this->getAvailabilityStats();
        $availabilityByType = $this->getAvailabilityByType();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'is_active' => $this->is_active,
            'capacity' => [
                'total_spots' => $availabilityStats['total_spots'],
                'available_spots' => $availabilityStats['available_spots'],
                'occupied_spots' => $availabilityStats['occupied_spots'],
                'reserved_spots' => $availabilityStats['reserved_spots'],
                'out_of_order_spots' => $availabilityStats['out_of_order_spots'],
                'occupancy_rate' => $availabilityStats['occupancy_rate'],
            ],
            'availability_by_type' => $availabilityByType,
            'spots' => ParkingSpotResource::collection($this->whenLoaded('parkingSpots')),
            'real_time_data' => [
                'last_updated' => now()->toISOString(),
                'availability_status' => $this->getAvailabilityStatus($availabilityStats['occupancy_rate']),
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get availability status based on occupancy rate
     */
    private function getAvailabilityStatus(float $occupancyRate): string
    {
        return match (true) {
            $occupancyRate >= 95 => 'full',
            $occupancyRate >= 80 => 'nearly_full',
            $occupancyRate >= 50 => 'moderate',
            $occupancyRate >= 20 => 'low_occupancy',
            default => 'mostly_empty',
        };
    }
}
