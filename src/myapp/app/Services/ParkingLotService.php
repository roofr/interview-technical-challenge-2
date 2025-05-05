<?php

namespace App\Services;

use App\Models\ParkingSpot;
use Illuminate\Support\Facades\Redis;

class ParkingLotService
{
    const AVAILABLE_SPOTS_KEY = 'parking-spots:available';

    const PARKING_SPOTS_SIZES = [
        'regular' => 1,
        'van' => 3,
    ];

    public function getAvailableSpotsCached(): int
    {
        $counter = Redis::get(self::AVAILABLE_SPOTS_KEY);

        if ($counter === null) {
            $counter = $this->getAvailableSpots();
            Redis::set(self::AVAILABLE_SPOTS_KEY, $counter);
        }

        return $counter;
    }

    public function getAvailableSpots(): int
    {
        return ParkingSpot::where(['available' => true])->count();
    }

    public function increaseAvailableSpots(int $type): int
    {
        return Redis::incr(self::AVAILABLE_SPOTS_KEY, self::PARKING_SPOTS_SIZES[$type]);
    }

    public function decreaseAvailableSpots(int $type): int
    {
        return Redis::decr(self::AVAILABLE_SPOTS_KEY, self::PARKING_SPOTS_SIZES[$type]);
    }
}
