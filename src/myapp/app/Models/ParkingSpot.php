<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ParkingSpot extends Model
{
    use HasFactory;

    protected $fillable = [
        'parking_lot_id',
        'spot_number',
        'spot_type',
        'status',
        'size_units',
        'floor_level',
        'section',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'size_units' => 'integer',
    ];

    /**
     * Get the parking lot that owns this spot
     */
    public function parkingLot(): BelongsTo
    {
        return $this->belongsTo(ParkingLot::class);
    }

    /**
     * Get the currently parked vehicle
     */
    public function currentVehicle(): HasOne
    {
        return $this->hasOne(Vehicle::class)->whereNull('unparked_at');
    }

    /**
     * Check if the spot is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->is_active;
    }

    /**
     * Check if the spot is occupied
     */
    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }

    /**
     * Check if a vehicle type can park in this spot
     */
    public function canAccommodateVehicle(string $vehicleType): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        return match ($vehicleType) {
            'motorcycle' => true, // Motorcycles can park anywhere
            'car' => in_array($this->spot_type, ['regular', 'van']), // Cars can use regular or van spots
            'van' => $this->spot_type === 'van', // Vans need van spots only
            default => false,
        };
    }

    /**
     * Mark the spot as occupied
     */
    public function markAsOccupied(): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        return $this->update(['status' => 'occupied']);
    }

    /**
     * Mark the spot as available
     */
    public function markAsAvailable(): bool
    {
        return $this->update(['status' => 'available']);
    }

    /**
     * Get the spot's full identifier
     */
    public function getFullIdentifierAttribute(): string
    {
        $identifier = $this->spot_number;

        if ($this->floor_level) {
            $identifier = "Floor {$this->floor_level} - {$identifier}";
        }

        if ($this->section) {
            $identifier = "Section {$this->section} - {$identifier}";
        }

        return $identifier;
    }

    /**
     * Scope for available spots
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    /**
     * Scope for occupied spots
     */
    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    /**
     * Scope for specific spot type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('spot_type', $type);
    }
}
