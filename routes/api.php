<?php

use App\Http\Controllers\Api\CleaningTaskController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\HostexWebhookController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\ReservationNoteController;
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

    Route::get('reservations/{reservation}/notes', [ReservationNoteController::class, 'index']);
    Route::post('reservations/{reservation}/notes', [ReservationNoteController::class, 'store']);
    Route::get('reservation-notes/{reservationNote}', [ReservationNoteController::class, 'show']);
    Route::put('reservation-notes/{reservationNote}', [ReservationNoteController::class, 'update']);
    Route::delete('reservation-notes/{reservationNote}', [ReservationNoteController::class, 'destroy']);

    Route::get('cleaning-tasks', [CleaningTaskController::class, 'index']);
    Route::get('cleaning-tasks/today', [CleaningTaskController::class, 'today']);
    Route::get('cleaning-tasks/{cleaningTask}', [CleaningTaskController::class, 'show']);
    Route::patch('cleaning-tasks/{cleaningTask}', [CleaningTaskController::class, 'update']);
    Route::patch('cleaning-tasks/{cleaningTask}/status', [CleaningTaskController::class, 'updateStatus']);
    Route::post('cleaning-tasks/{cleaningTask}/photos', [CleaningTaskController::class, 'storePhotos']);
    Route::post('cleaning-tasks/{cleaningTask}/complete', [CleaningTaskController::class, 'complete']);

    Route::get('contacts', [ContactController::class, 'index']);
    Route::get('contacts/{contact}', [ContactController::class, 'show']);
});
