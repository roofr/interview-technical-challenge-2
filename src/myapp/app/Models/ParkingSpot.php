<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use LogicException;

class ParkingSpot extends Model
{
    use HasFactory;

    const LOCK_KEY = 'lock-spot:';

    public function lock(): void
    {
        if (empty($this->id)) {
            throw new LogicException('Cannot lock unsaved parking spot');
        }

        Redis::set(self::LOCK_KEY . $this->id, true);
    }

    public function unlock(): void
    {
        if (empty($this->id)) {
            throw new LogicException('Cannot lock unsaved parking spot');
        }

        Redis::del(self::LOCK_KEY . $this->id);
    }

    public function parkAndSave(int $referenceId, string $make, string $model)
    {
        $this->available = false;
        $this->referenceId = $referenceId;
        $this->make = $make;
        $this->model = $model;

        $this->save();
    }

    public function unparkAndSave()
    {
        $this->available = true;
        $this->referenceId = null;
        $this->make = null;
        $this->model = null;

        $this->save();
    }
}
