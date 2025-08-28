<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ParkingSpotController;
use App\Http\Controllers\Api\ParkingLotController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Parking Management API Routes
|--------------------------------------------------------------------------
|
| These routes handle parking lot management operations including parking,
| unparking vehicles, and retrieving parking lot status information.
|
*/

// Parking Spot Operations
Route::post('/parking-spot/{id}/park', [ParkingSpotController::class, 'park'])
    ->where('id', '[0-9]+')
    ->name('parking-spot.park');

Route::post('/parking-spot/{id}/unpark', [ParkingSpotController::class, 'unpark'])
    ->where('id', '[0-9]+')
    ->name('parking-spot.unpark');

// Parking Lot Information
Route::get('/parking-lot', [ParkingLotController::class, 'show'])
    ->name('parking-lot.show');

Route::get('/parking-lot/{id}', [ParkingLotController::class, 'show'])
    ->where('id', '[0-9]+')
    ->name('parking-lot.show-specific');

// Additional helpful endpoints
Route::get('/parking-lots', [ParkingLotController::class, 'index'])
    ->name('parking-lots.index');

Route::get('/parking-lot/{id}/capacity', [ParkingLotController::class, 'capacity'])
    ->where('id', '[0-9]+')
    ->name('parking-lot.capacity');
