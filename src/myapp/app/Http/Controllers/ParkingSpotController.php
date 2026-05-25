<?php

namespace App\Http\Controllers;

use App\DTOs\ParkRequestDTO;
use App\Exceptions\ParkingException;
use App\Http\Requests\ParkRequest;
use App\Http\Resources\ParkingSessionResource;
use App\Services\ParkingService;
use Illuminate\Http\JsonResponse;

class ParkingSpotController extends Controller
{
    public function __construct(private readonly ParkingService $parkingService) {}

    public function park(ParkRequest $request, int $id): JsonResponse
    {
        try {
            $session = $this->parkingService->park($id, ParkRequestDTO::fromArray($request->validated()));

            $session->load('spots');

            return response()->json(new ParkingSessionResource($session), 201);
        } catch (ParkingException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function unpark(int $id): JsonResponse
    {
        try {
            $this->parkingService->unpark($id);

            return response()->json(['message' => 'Spot successfully freed.']);
        } catch (ParkingException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
