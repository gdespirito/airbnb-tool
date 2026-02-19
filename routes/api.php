<?php

use App\Http\Controllers\Api\CleaningTaskController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\HostexWebhookController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/hostex', HostexWebhookController::class);

Route::middleware('auth:sanctum')->prefix('v1')->group(function (): void {
    Route::get('properties', [PropertyController::class, 'index']);
    Route::get('properties/{property}', [PropertyController::class, 'show']);

    Route::get('reservations', [ReservationController::class, 'index']);
    Route::post('reservations', [ReservationController::class, 'store']);
    Route::get('reservations/{reservation}', [ReservationController::class, 'show']);
    Route::put('reservations/{reservation}', [ReservationController::class, 'update']);
    Route::delete('reservations/{reservation}', [ReservationController::class, 'destroy']);

    Route::get('cleaning-tasks', [CleaningTaskController::class, 'index']);
    Route::get('cleaning-tasks/{cleaningTask}', [CleaningTaskController::class, 'show']);
    Route::patch('cleaning-tasks/{cleaningTask}/status', [CleaningTaskController::class, 'updateStatus']);

    Route::get('contacts', [ContactController::class, 'index']);
    Route::get('contacts/{contact}', [ContactController::class, 'show']);
});
