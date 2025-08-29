<?php

namespace App\Services;

use App\Models\ParkingLot;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class ParkingLotService
{
    /**
     * Get comprehensive parking lot status
     */
    public function getParkingLotStatus(int $parkingLotId): array
    {
        $parkingLot = ParkingLot::with(['parkingSpots.currentVehicle'])->findOrFail($parkingLotId);
        
        $availabilityStats = $parkingLot->getAvailabilityStats();
        $availabilityByType = $parkingLot->getAvailabilityByType();
        
        return [
            'parking_lot' => [
                'id' => $parkingLot->id,
                'name' => $parkingLot->name,
                'location' => $parkingLot->location,
                'description' => $parkingLot->description,
                'is_active' => $parkingLot->is_active,
            ],
            'capacity' => [
                'total_spots' => $availabilityStats['total_spots'],
                'available_spots' => $availabilityStats['available_spots'],
                'occupied_spots' => $availabilityStats['occupied_spots'],
                'reserved_spots' => $availabilityStats['reserved_spots'],
                'out_of_order_spots' => $availabilityStats['out_of_order_spots'],
                'occupancy_rate' => $availabilityStats['occupancy_rate'],
            ],
            'availability_by_type' => $availabilityByType,
            'spots' => $this->formatSpotsData($parkingLot->parkingSpots),
            'real_time_data' => [
                'last_updated' => now()->toISOString(),
                'currently_parked_vehicles' => $this->getCurrentlyParkedVehicles($parkingLot),
            ]
        ];
    }
    
    /**
     * Get all parking lots with basic status
     */
    public function getAllParkingLots()
    {
        return ParkingLot::where('is_active', true)
            ->with(['parkingSpots'])
            ->get()
            ->map(function ($parkingLot) {
                $stats = $parkingLot->getAvailabilityStats();
                return [
                    'id' => $parkingLot->id,
                    'name' => $parkingLot->name,
                    'location' => $parkingLot->location,
                    'total_spots' => $stats['total_spots'],
                    'available_spots' => $stats['available_spots'],
                    'occupancy_rate' => $stats['occupancy_rate'],
                ];
            });
    }
    
    /**
     * Get real-time capacity monitoring data
     */
    public function getRealTimeCapacity(int $parkingLotId): array
    {
        $parkingLot = ParkingLot::findOrFail($parkingLotId);
        $stats = $parkingLot->getAvailabilityStats();
        $typeStats = $parkingLot->getAvailabilityByType();
        
        return [
            'parking_lot_id' => $parkingLot->id,
            'timestamp' => now()->toISOString(),
            'overall_capacity' => [
                'total_spots' => $stats['total_spots'],
                'available_spots' => $stats['available_spots'],
                'occupied_spots' => $stats['occupied_spots'],
                'occupancy_percentage' => $stats['occupancy_rate'],
            ],
            'capacity_by_vehicle_type' => [
                'motorcycle_spots' => [
                    'total' => $typeStats['motorcycle']['total'],
                    'available' => $typeStats['motorcycle']['available'],
                    'occupied' => $typeStats['motorcycle']['occupied'],
                ],
                'regular_spots' => [
                    'total' => $typeStats['regular']['total'],
                    'available' => $typeStats['regular']['available'],
                    'occupied' => $typeStats['regular']['occupied'],
                ],
                'van_spots' => [
                    'total' => $typeStats['van']['total'],
                    'available' => $typeStats['van']['available'],
                    'occupied' => $typeStats['van']['occupied'],
                ],
            ],
            'availability_status' => $this->getAvailabilityStatus($stats['occupancy_rate']),
        ];
    }
    
    /**
     * Format spots data for API response
     */
    private function formatSpotsData(Collection $spots): array
    {
        return $spots->map(function ($spot) {
            return [
                'id' => $spot->id,
                'spot_number' => $spot->spot_number,
                'spot_type' => $spot->spot_type,
                'status' => $spot->status,
                'floor_level' => $spot->floor_level,
                'section' => $spot->section,
                'is_active' => $spot->is_active,
                'current_vehicle' => $spot->currentVehicle ? [
                    'id' => $spot->currentVehicle->id,
                    'vehicle_type' => $spot->currentVehicle->vehicle_type,
                    'license_plate' => $spot->currentVehicle->license_plate,
                    'parked_at' => $spot->currentVehicle->parked_at,
                    'parking_duration' => $spot->currentVehicle->parking_duration_human,
                ] : null,
            ];
        })->toArray();
    }
    
    /**
     * Get currently parked vehicles summary
     */
    private function getCurrentlyParkedVehicles(ParkingLot $parkingLot): array
    {
        $vehicles = Vehicle::currentlyParked()
            ->inParkingLot($parkingLot->id)
            ->get();
            
        return [
            'total_count' => $vehicles->count(),
            'by_type' => [
                'motorcycles' => $vehicles->where('vehicle_type', 'motorcycle')->count(),
                'cars' => $vehicles->where('vehicle_type', 'car')->count(),
                'vans' => $vehicles->where('vehicle_type', 'van')->count(),
            ],
        ];
    }
    
    /**
     * Get availability status based on occupancy rate
     */
    private function getAvailabilityStatus(float $occupancyRate): string
    {
        return match (true) {
            $occupancyRate >= 95 => 'full',
            $occupancyRate >= 80 => 'nearly_full',
            $occupancyRate >= 50 => 'moderate',
            $occupancyRate >= 20 => 'low_occupancy',
            default => 'mostly_empty',
        };
    }
}
