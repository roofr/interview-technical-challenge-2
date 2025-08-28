<?php

namespace App\Enums;

enum VehicleType: string
{
    case MOTORCYCLE = 'motorcycle';
    case CAR = 'car';
    case VAN = 'van';

    /**
     * Get the space units required by this vehicle type
     */
    public function getSpaceUnits(): int
    {
        return match ($this) {
            self::MOTORCYCLE => 1,
            self::CAR => 1,
            self::VAN => 3,
        };
    }

    /**
     * Get all vehicle types as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get vehicle type display name
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::MOTORCYCLE => 'Motorcycle',
            self::CAR => 'Car',
            self::VAN => 'Van',
        };
    }

    /**
     * Check if this vehicle type can park in a given spot type
     */
    public function canParkInSpotType(SpotType $spotType): bool
    {
        return match ($this) {
            self::MOTORCYCLE => true, // Motorcycles can park anywhere
            self::CAR => in_array($spotType, [SpotType::REGULAR, SpotType::VAN]), // Cars can use regular or van spots
            self::VAN => $spotType === SpotType::VAN, // Vans need van spots only
        };
    }
}
