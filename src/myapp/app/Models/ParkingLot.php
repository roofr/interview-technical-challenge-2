<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'location',
        'total_regular_spots',
        'total_motorcycle_spots',
        'total_van_spots',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_regular_spots' => 'integer',
        'total_motorcycle_spots' => 'integer',
        'total_van_spots' => 'integer',
    ];

    /**
     * Get all parking spots for this parking lot
     */
    public function parkingSpots(): HasMany
    {
        return $this->hasMany(ParkingSpot::class);
    }

    /**
     * Get active parking spots
     */
    public function activeParkingSpots(): HasMany
    {
        return $this->parkingSpots()->where('is_active', true);
    }

    /**
     * Get available parking spots
     */
    public function availableSpots(): HasMany
    {
        return $this->activeParkingSpots()->where('status', 'available');
    }

    /**
     * Get occupied parking spots
     */
    public function occupiedSpots(): HasMany
    {
        return $this->activeParkingSpots()->where('status', 'occupied');
    }

    /**
     * Get total capacity of the parking lot
     */
    public function getTotalCapacityAttribute(): int
    {
        return $this->total_regular_spots + $this->total_motorcycle_spots + $this->total_van_spots;
    }

    /**
     * Get current availability statistics
     */
    public function getAvailabilityStats(): array
    {
        $spots = $this->activeParkingSpots()->get();

        $stats = [
            'total_spots' => $spots->count(),
            'available_spots' => $spots->where('status', 'available')->count(),
            'occupied_spots' => $spots->where('status', 'occupied')->count(),
            'reserved_spots' => $spots->where('status', 'reserved')->count(),
            'out_of_order_spots' => $spots->where('status', 'out_of_order')->count(),
        ];

        $stats['occupancy_rate'] = $stats['total_spots'] > 0
            ? round(($stats['occupied_spots'] / $stats['total_spots']) * 100, 2)
            : 0;

        return $stats;
    }

    /**
     * Get availability by spot type
     */
    public function getAvailabilityByType(): array
    {
        $spots = $this->activeParkingSpots()->get();

        return [
            'regular' => [
                'total' => $spots->where('spot_type', 'regular')->count(),
                'available' => $spots->where('spot_type', 'regular')->where('status', 'available')->count(),
                'occupied' => $spots->where('spot_type', 'regular')->where('status', 'occupied')->count(),
            ],
            'motorcycle' => [
                'total' => $spots->where('spot_type', 'motorcycle')->count(),
                'available' => $spots->where('spot_type', 'motorcycle')->where('status', 'available')->count(),
                'occupied' => $spots->where('spot_type', 'motorcycle')->where('status', 'occupied')->count(),
            ],
            'van' => [
                'total' => $spots->where('spot_type', 'van')->count(),
                'available' => $spots->where('spot_type', 'van')->where('status', 'available')->count(),
                'occupied' => $spots->where('spot_type', 'van')->where('status', 'occupied')->count(),
            ],
        ];
    }
}
