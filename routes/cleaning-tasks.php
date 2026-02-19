<?php

use App\Http\Controllers\CleaningTaskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('cleaning-tasks', [CleaningTaskController::class, 'index'])->name('cleaning-tasks.index');
    Route::get('cleaning-tasks/create', [CleaningTaskController::class, 'create'])->name('cleaning-tasks.create');
    Route::post('cleaning-tasks', [CleaningTaskController::class, 'store'])->name('cleaning-tasks.store');
    Route::get('cleaning-tasks/{cleaningTask}', [CleaningTaskController::class, 'show'])->name('cleaning-tasks.show');
    Route::get('cleaning-tasks/{cleaningTask}/edit', [CleaningTaskController::class, 'edit'])->name('cleaning-tasks.edit');
    Route::put('cleaning-tasks/{cleaningTask}', [CleaningTaskController::class, 'update'])->name('cleaning-tasks.update');
    Route::delete('cleaning-tasks/{cleaningTask}', [CleaningTaskController::class, 'destroy'])->name('cleaning-tasks.destroy');
});
