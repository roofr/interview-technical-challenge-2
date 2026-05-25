<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkingSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vehicle_type' => $this->vehicle_type->value,
            'license_plate' => $this->license_plate,
            'spots' => ParkingSpotResource::collection($this->whenLoaded('spots')),
        ];
    }
}
