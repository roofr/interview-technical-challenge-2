<?php

namespace App\Models;

use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParkingSession extends Model
{
    protected $fillable = ['vehicle_type', 'license_plate'];

    protected $casts = [
        'vehicle_type' => VehicleType::class,
    ];

    public function spots(): BelongsToMany
    {
        return $this->belongsToMany(ParkingSpot::class, 'parking_session_spot');
    }
}
