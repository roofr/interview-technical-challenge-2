<?php

namespace app\Http\Controllers\ParkingSpot;

use App\Http\Controllers\Controller;
use App\Models\ParkingSpot;
use App\Services\ParkingLotService;
use app\Services\ParkingSpotFinder;

class UnparkAction extends Controller
{
    private ParkingSpotFinder $finder;

    public function __construct(ParkingSpotFinder $finder)
    {
        $this->finder = $finder;
    }

    public function __invoke(int $id)
    {
        $unparked = $this->finder->release($id);

        if (!$unparked) {
            return response('Could not release the parking spot', 400);
        }

        return response('Unpark successful', 200);
    }
}
