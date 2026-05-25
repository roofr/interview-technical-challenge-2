<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParkingSection extends Model
{
    protected $fillable = ['parking_lot_id', 'name'];

    public function parkingLot(): BelongsTo
    {
        return $this->belongsTo(ParkingLot::class);
    }

    public function spots(): HasMany
    {
        return $this->hasMany(ParkingSpot::class);
    }
}
