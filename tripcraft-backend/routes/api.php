<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    DestinationController,
    TripController,
    ItineraryItemController,
    TripMemberController,
    TripCommentController,
    AdminController
};

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::get('/destinations', [DestinationController::class, 'index']);
Route::get('/destinations/{destination}', [DestinationController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('trips', TripController::class);
    Route::post('/trips/{trip}/generate-itinerary', [TripController::class, 'generate']);

    Route::get('/trips/{trip}/items', [ItineraryItemController::class, 'index']);
    Route::post('/trips/{trip}/items', [ItineraryItemController::class, 'store']);
    Route::put('/trips/{trip}/items/{item}', [ItineraryItemController::class, 'update']);
    Route::delete('/trips/{trip}/items/{item}', [ItineraryItemController::class, 'destroy']);

    Route::get('/trips/{trip}/members', [TripMemberController::class, 'index']);
    Route::post('/trips/{trip}/members', [TripMemberController::class, 'store']);
    Route::patch('/trips/{trip}/members/{member}', [TripMemberController::class, 'update']);
    Route::delete('/trips/{trip}/members/{member}', [TripMemberController::class, 'destroy']);

    Route::get('/trips/{trip}/comments', [TripCommentController::class, 'index']);
    Route::post('/trips/{trip}/comments', [TripCommentController::class, 'store']);

    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::patch('/users/{user}', [AdminController::class, 'updateUser']);
        Route::get('/trips/pending', [AdminController::class, 'pendingTrips']);
        Route::post('/trips/{trip}/approve', [AdminController::class, 'approveTrip']);
        Route::post('/trips/{trip}/reject', [AdminController::class, 'rejectTrip']);
        Route::post('/destinations', [DestinationController::class, 'store']);
        Route::put('/destinations/{destination}', [DestinationController::class, 'update']);
        Route::delete('/destinations/{destination}', [DestinationController::class, 'destroy']);
    });
});
