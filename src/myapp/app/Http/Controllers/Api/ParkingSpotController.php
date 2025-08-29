<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ParkingService;
use App\Http\Requests\ParkVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Http\Resources\ParkingSpotResource;
use App\Models\ParkingSpot;
use App\Models\Vehicle;
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

            // Get the created vehicle with its parking spot for proper Resource formatting
            $vehicle = Vehicle::with('parkingSpot')->find($result['data']['vehicle_id']);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle parked successfully',
                'data' => new VehicleResource($vehicle)
            ], 201);

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

            // Get the unparked vehicle and updated parking spot for proper Resource formatting
            $vehicle = Vehicle::find($result['data']['vehicle_id']);
            $parkingSpot = ParkingSpot::find($result['data']['spot_id']);

            return response()->json([
                'success' => true,
                'message' => 'Vehicle unparked successfully',
                'data' => [
                    'vehicle' => new VehicleResource($vehicle),
                    'parking_spot' => new ParkingSpotResource($parkingSpot),
                    'parking_duration_minutes' => $result['data']['parking_duration_minutes'],
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
