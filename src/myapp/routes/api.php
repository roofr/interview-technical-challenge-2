<?php

use App\Http\Controllers\ParkingLot\GetAvailableSpotsAction;
use App\Http\Controllers\ParkingSpot\ParkAction;
use App\Http\Controllers\ParkingSpot\UnparkAction;
use Illuminate\Support\Facades\Route;

Route::post('parking-spot/{id}/park', ParkAction::class);
Route::post('parking-spot/{id}/unpark', UnparkAction::class);

Route::get('parking-lot', GetAvailableSpotsAction::class);
