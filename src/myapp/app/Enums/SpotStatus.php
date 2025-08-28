<?php

namespace App\Enums;

enum SpotStatus: string
{
    case AVAILABLE = 'available';
    case OCCUPIED = 'occupied';
    case RESERVED = 'reserved';
    case OUT_OF_ORDER = 'out_of_order';

    /**
     * Get all spot statuses as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get spot status display name
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Available',
            self::OCCUPIED => 'Occupied',
            self::RESERVED => 'Reserved',
            self::OUT_OF_ORDER => 'Out of Order',
        };
    }

    /**
     * Get spot status color for UI representation
     */
    public function getColor(): string
    {
        return match ($this) {
            self::AVAILABLE => 'green',
            self::OCCUPIED => 'red',
            self::RESERVED => 'yellow',
            self::OUT_OF_ORDER => 'gray',
        };
    }

    /**
     * Check if the spot is available for parking
     */
    public function isAvailableForParking(): bool
    {
        return $this === self::AVAILABLE;
    }
}
