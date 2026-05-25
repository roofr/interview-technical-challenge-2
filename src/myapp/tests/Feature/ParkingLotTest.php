<?php

namespace Tests\Feature;

use App\Enums\SpotType;
use App\Models\ParkingLot;
use App\Models\ParkingSection;
use App\Models\ParkingSpot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkingLotTest extends TestCase
{
    use RefreshDatabase;

    private ParkingLot $lot;
    private ParkingSection $sectionA;
    private ParkingSection $sectionB;
    private ParkingSection $sectionD;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lot = ParkingLot::create(['name' => 'Test Lot']);
        $this->sectionA = ParkingSection::create(['parking_lot_id' => $this->lot->id, 'name' => 'A']);
        $this->sectionB = ParkingSection::create(['parking_lot_id' => $this->lot->id, 'name' => 'B']);
        $this->sectionD = ParkingSection::create(['parking_lot_id' => $this->lot->id, 'name' => 'D']);
    }

    private function makeSpot(ParkingSection $section, SpotType $type, int $number, bool $occupied = false): ParkingSpot
    {
        return ParkingSpot::create([
            'parking_section_id' => $section->id,
            'number' => $number,
            'type' => $type->value,
            'is_occupied' => $occupied,
        ]);
    }

    public function test_get_parking_lot_returns_correct_totals(): void
    {
        $this->makeSpot($this->sectionA, SpotType::Motorcycle, 1);
        $this->makeSpot($this->sectionA, SpotType::Motorcycle, 2, occupied: true);
        $this->makeSpot($this->sectionB, SpotType::Regular, 1);
        $this->makeSpot($this->sectionB, SpotType::Regular, 2);
        $this->makeSpot($this->sectionD, SpotType::Van, 1, occupied: true);

        $response = $this->getJson('/api/parking-lot');

        $response->assertOk()
            ->assertJsonFragment(['total_spots' => 5, 'available_spots' => 3])
            ->assertJsonPath('spots_by_type.motorcycle.total', 2)
            ->assertJsonPath('spots_by_type.motorcycle.available', 1)
            ->assertJsonPath('spots_by_type.regular.total', 2)
            ->assertJsonPath('spots_by_type.regular.available', 2)
            ->assertJsonPath('spots_by_type.van.total', 1)
            ->assertJsonPath('spots_by_type.van.available', 0);
    }

    public function test_get_parking_lot_returns_sections_with_spots(): void
    {
        $this->makeSpot($this->sectionA, SpotType::Motorcycle, 1);
        $this->makeSpot($this->sectionB, SpotType::Regular, 1);

        $response = $this->getJson('/api/parking-lot');

        $response->assertOk()
            ->assertJsonCount(3, 'sections') // A, B, D
            ->assertJsonPath('sections.0.name', 'A')
            ->assertJsonCount(1, 'sections.0.spots')
            ->assertJsonPath('sections.1.name', 'B')
            ->assertJsonCount(1, 'sections.1.spots');
    }

    public function test_get_parking_lot_returns_404_when_no_lot_exists(): void
    {
        $this->lot->delete();

        $this->getJson('/api/parking-lot')->assertNotFound();
    }
}
