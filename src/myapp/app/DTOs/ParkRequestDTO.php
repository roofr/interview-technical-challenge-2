<?php

namespace App\DTOs;

use App\Enums\VehicleType;

final class ParkRequestDTO
{
    public function __construct(
        public readonly VehicleType $vehicleType,
        public readonly ?string $licensePlate = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            vehicleType: VehicleType::from($data['vehicle_type']),
            licensePlate: $data['license_plate'] ?? null,
        );
    }
}
