<?php

namespace App\Http\Controllers\ParkingLot;

use App\Http\Controllers\Controller;
use App\Services\ParkingLotService;

class GetAvailableSpotsAction extends Controller
{
    private ParkingLotService $service;

    public function __construct(ParkingLotService $service)
    {
        $this->service = $service;
    }

    public function __invoke()
    {
        return $this->service->getAvailableSpotsCached();
    }
}
