<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'vehicle_type' => $this->vehicle_type,
            'license_plate' => $this->license_plate,
            'make' => $this->make,
            'model' => $this->model,
            'color' => $this->color,
            'parked_at' => $this->parked_at,
            'unparked_at' => $this->unparked_at,
            'parking_duration_minutes' => $this->when($this->parked_at, $this->parking_duration),
            'parking_duration_human' => $this->when($this->parked_at, $this->parking_duration_human),
            'is_currently_parked' => $this->isCurrentlyParked(),
            'required_space_units' => $this->required_space_units,
            'ai_detection_data' => $this->when($this->ai_detection_data, $this->ai_detection_data),
            'parking_spot_id' => $this->parking_spot_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
