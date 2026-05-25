<?php

namespace App\DTOs;

final class ParkingLotStatusDTO
{
    public function __construct(
        public readonly int $totalSpots,
        public readonly int $availableSpots,
        /** @var array<string, SpotTypeStatusDTO> */
        public readonly array $byType,
    ) {}
}
