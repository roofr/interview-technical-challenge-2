<?php

namespace App\Models;

use App\Enums\SpotType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParkingSpot extends Model
{
    protected $fillable = ['parking_section_id', 'number', 'type', 'is_occupied'];

    protected $casts = [
        'type' => SpotType::class,
        'is_occupied' => 'boolean',
        'number' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(ParkingSection::class, 'parking_section_id');
    }

    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(ParkingSession::class, 'parking_session_spot');
    }

    public function activeSession(): ?ParkingSession
    {
        return $this->sessions()->latest()->first();
    }
}
