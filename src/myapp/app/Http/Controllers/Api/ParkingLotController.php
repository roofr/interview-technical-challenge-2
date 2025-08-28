<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ParkingLotService;
use Illuminate\Http\JsonResponse;
use Exception;

class ParkingLotController extends Controller
{
    public function __construct(
        private ParkingLotService $parkingLotService
    ) {}

    /**
     * Get comprehensive parking lot status and availability
     *
     * @param int|null $id The parking lot ID (optional, defaults to first active lot)
     * @return JsonResponse
     */
    public function show(?int $id = null): JsonResponse
    {
        try {
            // If no ID provided, get the first active parking lot
            if (!$id) {
                $parkingLots = $this->parkingLotService->getAllParkingLots();
                if ($parkingLots->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No active parking lots found'
                    ], 404);
                }
                $id = $parkingLots->first()['id'];
            }

            $status = $this->parkingLotService->getParkingLotStatus($id);

            return response()->json([
                'success' => true,
                'data' => $status
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get all parking lots with basic information
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $parkingLots = $this->parkingLotService->getAllParkingLots();

            return response()->json([
                'success' => true,
                'data' => $parkingLots
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get real-time capacity monitoring data
     *
     * @param int $id The parking lot ID
     * @return JsonResponse
     */
    public function capacity(int $id): JsonResponse
    {
        try {
            $capacity = $this->parkingLotService->getRealTimeCapacity($id);

            return response()->json([
                'success' => true,
                'data' => $capacity
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
