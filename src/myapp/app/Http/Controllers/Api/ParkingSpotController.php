<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ParkingService;
use App\Http\Requests\ParkVehicleRequest;
use Illuminate\Http\JsonResponse;
use Exception;

class ParkingSpotController extends Controller
{
    public function __construct(
        private ParkingService $parkingService
    ) {}

    /**
     * Park a vehicle in a specific parking spot
     *
     * @param int $id The parking spot ID
     * @param ParkVehicleRequest $request
     * @return JsonResponse
     */
    public function park(int $id, ParkVehicleRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $result = $this->parkingService->parkVehicle($id, $validatedData);

            return response()->json($result, 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Unpark a vehicle from a specific parking spot
     *
     * @param int $id The parking spot ID
     * @return JsonResponse
     */
    public function unpark(int $id): JsonResponse
    {
        try {
            $result = $this->parkingService->unparkVehicle($id);

            return response()->json($result, 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
