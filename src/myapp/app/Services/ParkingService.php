<?php

namespace App\Services;

use App\Models\ParkingSpot;
use App\Models\Vehicle;
use App\Enums\VehicleType;
use Illuminate\Support\Facades\DB;
use Exception;

class ParkingService
{
    /**
     * Park a vehicle in a specific parking spot
     */
    public function parkVehicle(int $spotId, array $vehicleData): array
    {
        return DB::transaction(function () use ($spotId, $vehicleData) {
            $spot = ParkingSpot::findOrFail($spotId);

            // Check if spot is already occupied first
            if ($spot->isOccupied()) {
                throw new Exception('Parking spot is already occupied');
            }

            // Validate that the spot can accommodate the vehicle
            $this->validateParkingRequest($spot, $vehicleData['vehicle_type']);
            
            // Mark spot as occupied
            $spot->markAsOccupied();
            
            // Create vehicle record
            $vehicle = Vehicle::create([
                'parking_spot_id' => $spot->id,
                'vehicle_type' => $vehicleData['vehicle_type'],
                'license_plate' => $vehicleData['license_plate'] ?? null,
                'make' => $vehicleData['make'] ?? null,
                'model' => $vehicleData['model'] ?? null,
                'color' => $vehicleData['color'] ?? null,
                'parked_at' => now(),
                'ai_detection_data' => $vehicleData['ai_detection_data'] ?? null,
            ]);
            
            return [
                'success' => true,
                'message' => 'Vehicle parked successfully',
                'data' => [
                    'vehicle_id' => $vehicle->id,
                    'spot_id' => $spot->id,
                    'spot_number' => $spot->spot_number,
                    'parked_at' => $vehicle->parked_at,
                ]
            ];
        });
    }
    
    /**
     * Unpark a vehicle from a specific parking spot
     */
    public function unparkVehicle(int $spotId): array
    {
        return DB::transaction(function () use ($spotId) {
            $spot = ParkingSpot::findOrFail($spotId);
            
            // Check if spot is occupied
            if (!$spot->isOccupied()) {
                throw new Exception('Parking spot is not currently occupied');
            }
            
            // Find the currently parked vehicle
            $vehicle = $spot->currentVehicle;
            
            if (!$vehicle) {
                throw new Exception('No vehicle found in this parking spot');
            }
            
            // Mark vehicle as unparked
            $vehicle->markAsUnparked();
            
            // Mark spot as available
            $spot->markAsAvailable();
            
            return [
                'success' => true,
                'message' => 'Vehicle unparked successfully',
                'data' => [
                    'vehicle_id' => $vehicle->id,
                    'spot_id' => $spot->id,
                    'spot_number' => $spot->spot_number,
                    'unparked_at' => $vehicle->unparked_at,
                    'parking_duration_minutes' => $vehicle->parking_duration,
                ]
            ];
        });
    }
    
    /**
     * Find the best available spot for a vehicle type
     */
    public function findBestAvailableSpot(int $parkingLotId, string $vehicleType): ?ParkingSpot
    {
        $vehicleTypeEnum = VehicleType::from($vehicleType);
        
        // Priority order for spot selection
        $spotPriorities = match ($vehicleTypeEnum) {
            VehicleType::MOTORCYCLE => ['motorcycle', 'regular', 'van'],
            VehicleType::CAR => ['regular', 'van'],
            VehicleType::VAN => ['van'],
        };
        
        foreach ($spotPriorities as $spotType) {
            $spot = ParkingSpot::where('parking_lot_id', $parkingLotId)
                ->where('spot_type', $spotType)
                ->available()
                ->orderBy('spot_number')
                ->first();
                
            if ($spot) {
                return $spot;
            }
        }
        
        return null;
    }
    
    /**
     * Validate parking request
     */
    private function validateParkingRequest(ParkingSpot $spot, string $vehicleType): void
    {
        if (!$spot->is_active) {
            throw new Exception('Parking spot is not active');
        }

        // Check vehicle type compatibility without checking availability
        $canPark = match ($vehicleType) {
            'motorcycle' => true, // Motorcycles can park anywhere
            'car' => in_array($spot->spot_type, ['regular', 'van']), // Cars can use regular or van spots
            'van' => $spot->spot_type === 'van', // Vans need van spots only
            default => false,
        };

        if (!$canPark) {
            throw new Exception("Vehicle type '{$vehicleType}' cannot park in spot type '{$spot->spot_type}'");
        }
    }
}
