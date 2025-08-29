<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'parking_spot_id',
        'vehicle_type',
        'license_plate',
        'make',
        'model',
        'color',
        'parked_at',
        'unparked_at',
        'ai_detection_data',
    ];

    protected $casts = [
        'parked_at' => 'datetime',
        'unparked_at' => 'datetime',
        'ai_detection_data' => 'array',
    ];

    /**
     * Get the parking spot where this vehicle is parked
     */
    public function parkingSpot(): BelongsTo
    {
        return $this->belongsTo(ParkingSpot::class);
    }

    /**
     * Check if the vehicle is currently parked
     */
    public function isCurrentlyParked(): bool
    {
        return !is_null($this->parked_at) && is_null($this->unparked_at);
    }

    /**
     * Get the parking duration in minutes
     */
    public function getParkingDurationAttribute(): ?int
    {
        if (!$this->parked_at) {
            return null;
        }

        $endTime = $this->unparked_at ?? now();
        return $this->parked_at->diffInMinutes($endTime);
    }

    /**
     * Get the parking duration in a human-readable format
     */
    public function getParkingDurationHumanAttribute(): ?string
    {
        if (!$this->parked_at) {
            return null;
        }

        $endTime = $this->unparked_at ?? now();
        return $this->parked_at->diffForHumans($endTime, true);
    }

    /**
     * Mark the vehicle as unparked
     */
    public function markAsUnparked(): bool
    {
        if (!$this->isCurrentlyParked()) {
            return false;
        }

        return $this->update([
            'unparked_at' => now(),
            'parking_spot_id' => null,
        ]);
    }

    /**
     * Get the space units required by this vehicle type
     */
    public function getRequiredSpaceUnitsAttribute(): int
    {
        return match ($this->vehicle_type) {
            'motorcycle' => 1,
            'car' => 1,
            'van' => 3,
            default => 1,
        };
    }

    /**
     * Scope for currently parked vehicles
     */
    public function scopeCurrentlyParked($query)
    {
        return $query->whereNotNull('parked_at')->whereNull('unparked_at');
    }

    /**
     * Scope for vehicles of a specific type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('vehicle_type', $type);
    }

    /**
     * Scope for vehicles parked in a specific parking lot
     */
    public function scopeInParkingLot($query, int $parkingLotId)
    {
        return $query->whereHas('parkingSpot', function ($q) use ($parkingLotId) {
            $q->where('parking_lot_id', $parkingLotId);
        });
    }
}
