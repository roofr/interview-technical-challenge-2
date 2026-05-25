<?php

namespace Tests\Feature;

use App\Enums\SpotType;
use App\Enums\VehicleType;
use App\Models\ParkingLot;
use App\Models\ParkingSection;
use App\Models\ParkingSpot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParkingSpotTest extends TestCase
{
    use RefreshDatabase;

    private ParkingLot $lot;
    private ParkingSection $sectionA;
    private ParkingSection $sectionB;
    private ParkingSection $sectionC;
    private ParkingSection $sectionD;

    protected function setUp(): void
    {
        parent::setUp();

        $this->lot = ParkingLot::create(['name' => 'Test Lot']);
        $this->sectionA = ParkingSection::create(['parking_lot_id' => $this->lot->id, 'name' => 'A']);
        $this->sectionB = ParkingSection::create(['parking_lot_id' => $this->lot->id, 'name' => 'B']);
        $this->sectionC = ParkingSection::create(['parking_lot_id' => $this->lot->id, 'name' => 'C']);
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

    public function test_motorcycle_parks_in_regular_spot(): void
    {
        $spot = $this->makeSpot($this->sectionB, SpotType::Regular, 15);

        $response = $this->postJson("/api/parking-spot/{$spot->id}/park", [
            'vehicle_type' => VehicleType::Motorcycle->value,
        ]);

        $response->assertCreated()
            ->assertJsonFragment(['vehicle_type' => 'motorcycle']);

        $this->assertDatabaseHas('parking_spots', ['id' => $spot->id, 'is_occupied' => true]);
    }

    public function test_motorcycle_parks_in_motorcycle_spot(): void
    {
        $spot = $this->makeSpot($this->sectionA, SpotType::Motorcycle, 1);

        $this->postJson("/api/parking-spot/{$spot->id}/park", ['vehicle_type' => 'motorcycle'])
            ->assertCreated();

        $this->assertDatabaseHas('parking_spots', ['id' => $spot->id, 'is_occupied' => true]);
    }

    public function test_motorcycle_parks_in_van_spot(): void
    {
        $spot = $this->makeSpot($this->sectionD, SpotType::Van, 1);

        $this->postJson("/api/parking-spot/{$spot->id}/park", ['vehicle_type' => 'motorcycle'])
            ->assertCreated();
    }

    public function test_car_parks_in_regular_spot(): void
    {
        $spot = $this->makeSpot($this->sectionB, SpotType::Regular, 1);

        $this->postJson("/api/parking-spot/{$spot->id}/park", ['vehicle_type' => 'car'])
            ->assertCreated();
    }

    public function test_car_parks_in_van_spot(): void
    {
        $spot = $this->makeSpot($this->sectionD, SpotType::Van, 1);

        $this->postJson("/api/parking-spot/{$spot->id}/park", ['vehicle_type' => 'car'])
            ->assertCreated();
    }

    public function test_car_cannot_park_in_motorcycle_spot(): void
    {
        $spot = $this->makeSpot($this->sectionA, SpotType::Motorcycle, 1);

        $this->postJson("/api/parking-spot/{$spot->id}/park", ['vehicle_type' => 'car'])
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'A car cannot park in a motorcycle spot.']);
    }

    public function test_van_parks_in_dedicated_van_spot(): void
    {
        $spot = $this->makeSpot($this->sectionD, SpotType::Van, 1);

        $this->postJson("/api/parking-spot/{$spot->id}/park", ['vehicle_type' => 'van'])
            ->assertCreated()
            ->assertJsonFragment(['vehicle_type' => 'van']);

        $this->assertDatabaseHas('parking_spots', ['id' => $spot->id, 'is_occupied' => true]);
    }

    public function test_van_cannot_park_in_motorcycle_spot(): void
    {
        $spot = $this->makeSpot($this->sectionA, SpotType::Motorcycle, 1);

        $this->postJson("/api/parking-spot/{$spot->id}/park", ['vehicle_type' => 'van'])
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'A van cannot park in a motorcycle spot.']);
    }

    public function test_van_parks_in_three_consecutive_regular_spots(): void
    {
        $c3 = $this->makeSpot($this->sectionC, SpotType::Regular, 3);
        $c4 = $this->makeSpot($this->sectionC, SpotType::Regular, 4);
        $c5 = $this->makeSpot($this->sectionC, SpotType::Regular, 5);

        $response = $this->postJson("/api/parking-spot/{$c3->id}/park", ['vehicle_type' => 'van']);

        $response->assertCreated();

        foreach ([$c3->id, $c4->id, $c5->id] as $spotId) {
            $this->assertDatabaseHas('parking_spots', ['id' => $spotId, 'is_occupied' => true]);
        }

        $sessionId = $response->json('id');
        $this->assertDatabaseCount('parking_session_spot', 3);
        $this->assertDatabaseHas('parking_session_spot', ['parking_session_id' => $sessionId, 'parking_spot_id' => $c3->id]);
        $this->assertDatabaseHas('parking_session_spot', ['parking_session_id' => $sessionId, 'parking_spot_id' => $c4->id]);
        $this->assertDatabaseHas('parking_session_spot', ['parking_session_id' => $sessionId, 'parking_spot_id' => $c5->id]);
    }

    public function test_van_fails_when_consecutive_spots_not_available(): void
    {
        $c3 = $this->makeSpot($this->sectionC, SpotType::Regular, 3);
        $c4 = $this->makeSpot($this->sectionC, SpotType::Regular, 4);
        // C5 missing — not enough consecutive spots

        $this->postJson("/api/parking-spot/{$c3->id}/park", ['vehicle_type' => 'van'])
            ->assertUnprocessable();
    }

    public function test_van_fails_when_middle_consecutive_spot_is_occupied(): void
    {
        $c3 = $this->makeSpot($this->sectionC, SpotType::Regular, 3);
        $c4 = $this->makeSpot($this->sectionC, SpotType::Regular, 4, occupied: true);
        $c5 = $this->makeSpot($this->sectionC, SpotType::Regular, 5);

        $this->postJson("/api/parking-spot/{$c3->id}/park", ['vehicle_type' => 'van'])
            ->assertUnprocessable();
    }

    public function test_van_cannot_use_spots_from_different_sections(): void
    {
        $c3 = $this->makeSpot($this->sectionC, SpotType::Regular, 3);
        $c4 = $this->makeSpot($this->sectionC, SpotType::Regular, 4);
        $this->makeSpot($this->sectionB, SpotType::Regular, 5); // different section

        $this->postJson("/api/parking-spot/{$c3->id}/park", ['vehicle_type' => 'van'])
            ->assertUnprocessable();
    }

    public function test_unpark_frees_single_spot(): void
    {
        $spot = $this->makeSpot($this->sectionB, SpotType::Regular, 1);

        $this->postJson("/api/parking-spot/{$spot->id}/park", ['vehicle_type' => 'car']);
        $this->postJson("/api/parking-spot/{$spot->id}/unpark")->assertOk();

        $this->assertDatabaseHas('parking_spots', ['id' => $spot->id, 'is_occupied' => false]);
        $this->assertDatabaseCount('parking_sessions', 0);
    }

    public function test_unpark_van_frees_all_three_spots(): void
    {
        $c3 = $this->makeSpot($this->sectionC, SpotType::Regular, 3);
        $c4 = $this->makeSpot($this->sectionC, SpotType::Regular, 4);
        $c5 = $this->makeSpot($this->sectionC, SpotType::Regular, 5);

        $this->postJson("/api/parking-spot/{$c3->id}/park", ['vehicle_type' => 'van']);

        // Unpark via C4 — any of the 3 spots should free all
        $this->postJson("/api/parking-spot/{$c4->id}/unpark")->assertOk();

        foreach ([$c3->id, $c4->id, $c5->id] as $spotId) {
            $this->assertDatabaseHas('parking_spots', ['id' => $spotId, 'is_occupied' => false]);
        }

        $this->assertDatabaseCount('parking_sessions', 0);
        $this->assertDatabaseCount('parking_session_spot', 0);
    }

    public function test_unpark_fails_when_spot_is_not_occupied(): void
    {
        $spot = $this->makeSpot($this->sectionB, SpotType::Regular, 1);

        $this->postJson("/api/parking-spot/{$spot->id}/unpark")
            ->assertUnprocessable();
    }

    public function test_cannot_park_in_already_occupied_spot(): void
    {
        $spot = $this->makeSpot($this->sectionB, SpotType::Regular, 1);

        $this->postJson("/api/parking-spot/{$spot->id}/park", ['vehicle_type' => 'car']);
        $this->postJson("/api/parking-spot/{$spot->id}/park", ['vehicle_type' => 'car'])
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => "Spot #{$spot->id} is already occupied."]);
    }

    public function test_park_requires_vehicle_type(): void
    {
        $spot = $this->makeSpot($this->sectionB, SpotType::Regular, 1);

        $this->postJson("/api/parking-spot/{$spot->id}/park", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['vehicle_type']);
    }

    public function test_park_rejects_invalid_vehicle_type(): void
    {
        $spot = $this->makeSpot($this->sectionB, SpotType::Regular, 1);

        $this->postJson("/api/parking-spot/{$spot->id}/park", ['vehicle_type' => 'truck'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['vehicle_type']);
    }
}
