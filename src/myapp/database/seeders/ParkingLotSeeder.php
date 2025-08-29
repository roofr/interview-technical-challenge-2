<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParkingLot;
use App\Models\ParkingSpot;

class ParkingLotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main parking lot
        $parkingLot = ParkingLot::create([
            'name' => 'Downtown Central Parking',
            'description' => 'Multi-level parking facility in the heart of downtown',
            'location' => '123 Main Street, Downtown',
            'total_regular_spots' => 150,
            'total_motorcycle_spots' => 30,
            'total_van_spots' => 20,
            'is_active' => true,
        ]);

        // Create parking spots
        $this->createParkingSpots($parkingLot);

        // Create a second smaller parking lot for testing
        $smallLot = ParkingLot::create([
            'name' => 'Shopping Mall Parking',
            'description' => 'Convenient parking for mall visitors',
            'location' => '456 Commerce Ave, Shopping District',
            'total_regular_spots' => 80,
            'total_motorcycle_spots' => 15,
            'total_van_spots' => 10,
            'is_active' => true,
        ]);

        $this->createParkingSpots($smallLot, true);
    }

    /**
     * Create parking spots for a parking lot
     */
    private function createParkingSpots(ParkingLot $parkingLot, bool $isSmallLot = false): void
    {
        $floors = $isSmallLot ? ['Ground', 'Level 1'] : ['Ground', 'Level 1', 'Level 2', 'Level 3'];
        $sections = ['A', 'B', 'C'];

        $spotNumber = 1;

        foreach ($floors as $floor) {
            foreach ($sections as $section) {
                // Regular spots
                $regularSpotsPerSection = $isSmallLot ? 13 : 25;
                for ($i = 1; $i <= $regularSpotsPerSection; $i++) {
                    ParkingSpot::create([
                        'parking_lot_id' => $parkingLot->id,
                        'spot_number' => sprintf('%s%03d', $section, $spotNumber),
                        'spot_type' => 'regular',
                        'status' => 'available',
                        'size_units' => 1,
                        'floor_level' => $floor,
                        'section' => $section,
                        'is_active' => true,
                    ]);
                    $spotNumber++;
                }

                // Motorcycle spots
                $motorcycleSpotsPerSection = $isSmallLot ? 2 : 5;
                for ($i = 1; $i <= $motorcycleSpotsPerSection; $i++) {
                    ParkingSpot::create([
                        'parking_lot_id' => $parkingLot->id,
                        'spot_number' => sprintf('M%s%03d', $section, $spotNumber),
                        'spot_type' => 'motorcycle',
                        'status' => 'available',
                        'size_units' => 1,
                        'floor_level' => $floor,
                        'section' => $section,
                        'is_active' => true,
                    ]);
                    $spotNumber++;
                }

                // Van spots (fewer per section)
                $vanSpotsPerSection = $isSmallLot ? 1 : 3;
                if ($section === 'A') { // Only create van spots in section A
                    for ($i = 1; $i <= $vanSpotsPerSection; $i++) {
                        ParkingSpot::create([
                            'parking_lot_id' => $parkingLot->id,
                            'spot_number' => sprintf('V%s%03d', $section, $spotNumber),
                            'spot_type' => 'van',
                            'status' => 'available',
                            'size_units' => 3,
                            'floor_level' => $floor,
                            'section' => $section,
                            'is_active' => true,
                        ]);
                        $spotNumber++;
                    }
                }
            }
        }

        // Add a few out-of-order spots for testing
        ParkingSpot::where('parking_lot_id', $parkingLot->id)
            ->take(2)
            ->update(['status' => 'out_of_order']);
    }
}
