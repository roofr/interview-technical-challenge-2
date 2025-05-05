<?php

namespace App\Http\Controllers\ParkingSpot;

use App\Http\Controllers\Controller;
use App\Services\ParkingLotService;
use app\Services\ParkingSpotFinder;
use Illuminate\Http\Request;

class ParkAction extends Controller
{
    private ParkingSpotFinder $finder;

    public function __construct(ParkingSpotFinder $finder)
    {
        $this->finder = $finder;
    }

    public function __invoke(int $id, Request $request)
    {
        $make = $request->get('make');
        $model = $request->get('model');
        $type = $request->get('type');

        if (
            $make === null ||
            $model === null ||
            in_array($type, array_keys(ParkingLotService::PARKING_SPOTS_SIZES))
        ) {
            return response('Please specify make, model and type', 400);
        }

        $parked = $this->finder->reserve($id, $type, $make, $model);

        if (!$parked) {
            return response('Parking spot not available', 404);
        }

        return response(
            sprintf('%s %s parked successfully', $make, $model),
            200
        );
    }
}
