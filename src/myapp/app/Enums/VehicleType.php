<?php

namespace App\Enums;

enum VehicleType: string
{
    case Motorcycle = 'motorcycle';
    case Car = 'car';
    case Van = 'van';

    /**
     * @return SpotType[]
     */
    public function acceptedSpotTypes(): array
    {
        return match ($this) {
            self::Motorcycle => [SpotType::Motorcycle, SpotType::Regular, SpotType::Van],
            default => [SpotType::Regular, SpotType::Van],
        };
    }

    public function requiresConsecutiveSpots(): bool
    {
        return $this === self::Van;
    }

    public function consecutiveSpotsNeeded(): int
    {
        return match ($this) {
            self::Van => 3,
            default => 1,
        };
    }
}
