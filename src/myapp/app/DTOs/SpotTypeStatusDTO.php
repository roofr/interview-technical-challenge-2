<?php

namespace App\DTOs;

final class SpotTypeStatusDTO
{
    public function __construct(
        public readonly int $total,
        public readonly int $available,
    ) {}
}
