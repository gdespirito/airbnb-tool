<?php

namespace App\Actions\CleaningTasks;

use App\Enums\CleaningTaskStatus;
use App\Models\CleaningTask;
use App\Models\Reservation;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SkipStaleCleaningTasks
{
    /**
     * Marca como skipped las cleaning tasks activas de una propiedad con
     * scheduled_date anterior al threshold dado.
     *
     * Uso típico: al pasar una reserva a checked_in, las tasks viejas de
     * esa propiedad ya no tienen sentido operativo.
     *
     * @return int cantidad de tasks afectadas
     */
    public function forProperty(int $propertyId, CarbonInterface $before): int
    {
        try {
            return DB::transaction(function () use ($propertyId, $before): int {
                $affected = CleaningTask::query()
                    ->where('property_id', $propertyId)
                    ->whereDate('scheduled_date', '<', $before)
                    ->active()
                    ->update(['status' => CleaningTaskStatus::Skipped]);

                Log::info('Stale cleaning tasks skipped', [
                    'property_id' => $propertyId,
                    'before' => $before->toDateString(),
                    'affected' => $affected,
                ]);

                return $affected;
            });
        } catch (Throwable $e) {
            Log::error('Failed to skip stale cleaning tasks', [
                'property_id' => $propertyId,
                'before' => $before->toDateString(),
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Marca como skipped la cleaning task asociada a una reserva cancelada.
     * No va a haber limpieza porque la reserva ya no existe operativamente.
     *
     * @return int 0 o 1 (la reserva tiene a lo sumo una cleaning task)
     */
    public function forCancelledReservation(Reservation $reservation): int
    {
        $task = $reservation->cleaningTask;

        if ($task === null || ! $task->status->isActive()) {
            return 0;
        }

        try {
            return DB::transaction(function () use ($task, $reservation): int {
                $task->update(['status' => CleaningTaskStatus::Skipped]);

                Log::info('Cleaning task skipped for cancelled reservation', [
                    'reservation_id' => $reservation->id,
                    'cleaning_task_id' => $task->id,
                    'property_id' => $reservation->property_id,
                ]);

                return 1;
            });
        } catch (Throwable $e) {
            Log::error('Failed to skip cleaning task for cancelled reservation', [
                'reservation_id' => $reservation->id,
                'cleaning_task_id' => $task->id,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
