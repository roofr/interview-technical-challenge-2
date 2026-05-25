<?php

namespace App\Exceptions;

use RuntimeException;

class ParkingException extends RuntimeException
{
    public static function spotNotAvailable(int $spotId): self
    {
        return new self("Spot #$spotId is already occupied.");
    }

    public static function incompatibleVehicle(string $vehicleType, string $spotType): self
    {
        return new self("A $vehicleType cannot park in a $spotType spot.");
    }

    public static function noConsecutiveSpotsAvailable(int $count): self
    {
        return new self("No $count consecutive regular spots available starting from the requested spot.");
    }

    public static function spotNotOccupied(int $spotId): self
    {
        return new self("Spot #$spotId is not currently occupied.");
    }
}
