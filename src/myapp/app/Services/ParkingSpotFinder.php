<?php

namespace app\Services;

use App\Models\ParkingSpot;
use Illuminate\Support\Facades\Redis;

/**
 * Assumptions:
 *   - parking spot IDs are in order
 *   - cars park in order (take the first available spot, doesn't choose)
 */
class ParkingSpotFinder
{
    private ParkingLotService $service;

    public function __construct(ParkingLotService $service)
    {
        $this->service = $service;
    }

    public function reserve(int $id, int $type, string $make, string $model): bool
    {
        for ($i = $id; $i <= ParkingLotService::PARKING_SPOTS_SIZES[$type]; $i++) {
            $locked = \Redis::get(ParkingSpot::LOCK_KEY . $i);

            if ($locked) {
                return false;
            }

            /** @var ParkingSpot $parkingSpot */
            $parkingSpot = ParkingSpot::findOrFail($i);

            if ($parkingSpot->available === false) {
                return false;
            }

            $parkingSpot->lock();

            $parkingSpot->parkAndSave($id, $make, $model);
        }

        $this->service->increaseAvailableSpots($type);

        return true;
    }

    public function release(int $id)
    {
        /** @var ParkingSpot[] $reserved */
        $reserved = ParkingSpot::where('referenceId', $id)->get();
        $type = $reserved->first()->type;

        foreach ($reserved as $parkingSpot) {
            $parkingSpot->unparkAndSave();
            $parkingSpot->unlock();
        }

        $this->service->decreaseAvailableSpots($type);
    }
}
