<?php

namespace App\Http\Controllers;

use App\Http\Resources\ParkingLotResource;
use App\Models\ParkingLot;
use App\Services\ParkingService;
use Illuminate\Http\JsonResponse;

class ParkingLotController extends Controller
{
    public function index(ParkingService $parkingService): JsonResponse
    {
        // Assumption: single-lot MVP — always returns the first lot.
        // Could be extended to /api/parking-lot/{id} when multi-lot support is needed.
        $lot = ParkingLot::with('sections.spots')->firstOrFail();

        $status = $parkingService->getLotStatus($lot->id);

        $resource = (new ParkingLotResource($lot))->additional(['status' => $status]);

        return response()->json($resource);
    }
}
