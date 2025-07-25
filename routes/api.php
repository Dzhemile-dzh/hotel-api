<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\RoomTypeController;

Route::apiResource('bookings', BookingController::class);
Route::apiResource('guests', GuestController::class);
Route::apiResource('rooms', RoomController::class);
Route::apiResource('room-types', RoomTypeController::class); 