<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkingSpotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'spot_number' => $this->spot_number,
            'spot_type' => $this->spot_type,
            'status' => $this->status,
            'size_units' => $this->size_units,
            'floor_level' => $this->floor_level,
            'section' => $this->section,
            'is_active' => $this->is_active,
            'full_identifier' => $this->full_identifier,
            'current_vehicle' => new VehicleResource($this->whenLoaded('currentVehicle')),
            'parking_lot_id' => $this->parking_lot_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
