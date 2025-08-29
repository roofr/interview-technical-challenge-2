<?php

namespace App\Enums;

enum SpotType: string
{
    case REGULAR = 'regular';
    case MOTORCYCLE = 'motorcycle';
    case VAN = 'van';

    /**
     * Get the size units for this spot type
     */
    public function getSizeUnits(): int
    {
        return match ($this) {
            self::REGULAR => 1,
            self::MOTORCYCLE => 1,
            self::VAN => 3,
        };
    }

    /**
     * Get all spot types as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get spot type display name
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::REGULAR => 'Regular',
            self::MOTORCYCLE => 'Motorcycle',
            self::VAN => 'Van',
        };
    }

    /**
     * Get compatible vehicle types for this spot
     */
    public function getCompatibleVehicleTypes(): array
    {
        return match ($this) {
            self::REGULAR => [VehicleType::MOTORCYCLE, VehicleType::CAR],
            self::MOTORCYCLE => [VehicleType::MOTORCYCLE],
            self::VAN => [VehicleType::MOTORCYCLE, VehicleType::CAR, VehicleType::VAN],
        };
    }
}
