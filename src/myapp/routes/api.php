<?php

use App\Http\Controllers\ParkingLotController;
use App\Http\Controllers\ParkingSpotController;
use Illuminate\Support\Facades\Route;

Route::post('/parking-spot/{id}/park', [ParkingSpotController::class, 'park']);
Route::post('/parking-spot/{id}/unpark', [ParkingSpotController::class, 'unpark']);
Route::get('/parking-lot', [ParkingLotController::class, 'index']);
