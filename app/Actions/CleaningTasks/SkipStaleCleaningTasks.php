<?php

namespace App\Actions\CleaningTasks;

use App\Enums\CleaningTaskStatus;
use App\Models\CleaningTask;
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
}
