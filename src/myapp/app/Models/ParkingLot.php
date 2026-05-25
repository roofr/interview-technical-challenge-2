<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ParkingLot extends Model
{
    protected $fillable = ['name'];

    public function sections(): HasMany
    {
        return $this->hasMany(ParkingSection::class);
    }

    public function spots(): HasManyThrough
    {
        return $this->hasManyThrough(ParkingSpot::class, ParkingSection::class);
    }
}
