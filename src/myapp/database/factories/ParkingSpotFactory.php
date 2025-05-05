<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ParkingSpotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'available' => true,
        ];
    }
}
