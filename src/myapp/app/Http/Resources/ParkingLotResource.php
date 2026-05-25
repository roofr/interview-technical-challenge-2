<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkingLotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->additional['status'];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'total_spots' => $status->totalSpots,
            'available_spots' => $status->availableSpots,
            'spots_by_type' => collect($status->byType)->map(fn ($s) => [
                'total' => $s->total,
                'available' => $s->available,
            ]),
            'sections' => ParkingSectionResource::collection($this->whenLoaded('sections')),
        ];
    }
}
