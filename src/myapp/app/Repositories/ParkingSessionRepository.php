<?php

namespace App\Repositories;

use App\Enums\VehicleType;
use App\Models\ParkingSession;
use Illuminate\Database\Eloquent\Collection;

class ParkingSessionRepository
{
    public function create(VehicleType $vehicleType, Collection $spots, ?string $licensePlate = null): ParkingSession
    {
        $session = ParkingSession::create([
            'vehicle_type' => $vehicleType->value,
            'license_plate' => $licensePlate,
        ]);

        $session->spots()->attach($spots->pluck('id'));

        return $session;
    }

    public function findBySpotId(int $spotId): ?ParkingSession
    {
        return ParkingSession::whereHas('spots', fn ($q) => $q->where('parking_spots.id', $spotId))
            ->with('spots')
            ->first();
    }

    public function delete(ParkingSession $session): void
    {
        $session->spots()->detach();
        $session->delete();
    }
}
