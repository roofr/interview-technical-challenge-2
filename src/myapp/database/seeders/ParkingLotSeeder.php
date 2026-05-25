<?php

namespace Database\Seeders;

use App\Enums\SpotType;
use App\Models\ParkingLot;
use App\Models\ParkingSection;
use App\Models\ParkingSpot;
use Illuminate\Database\Seeder;

class ParkingLotSeeder extends Seeder
{
    public function run(): void
    {
        $lot = ParkingLot::create(['name' => 'Main Parking Lot']);

        $sections = [
            ['name' => 'A', 'count' => 10, 'type' => SpotType::Motorcycle],
            ['name' => 'B', 'count' => 20, 'type' => SpotType::Regular],
            ['name' => 'C', 'count' => 15, 'type' => SpotType::Regular],
            ['name' => 'D', 'count' => 5, 'type' => SpotType::Van],
        ];

        foreach ($sections as $def) {
            $section = ParkingSection::create([
                'parking_lot_id' => $lot->id,
                'name' => $def['name'],
            ]);

            $spots = [];
            for ($i = 1; $i <= $def['count']; $i++) {
                $spots[] = [
                    'parking_section_id' => $section->id,
                    'number' => $i,
                    'type' => $def['type']->value,
                    'is_occupied' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            ParkingSpot::insert($spots);
        }
    }
}
