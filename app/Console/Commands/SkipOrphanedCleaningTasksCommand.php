<?php

namespace App\Console\Commands;

use App\Enums\CleaningTaskStatus;
use App\Enums\ReservationStatus;
use App\Models\CleaningTask;
use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SkipOrphanedCleaningTasksCommand extends Command
{
    protected $signature = 'cleaning:skip-orphans
        {--dry-run : Listar qué se marcaría skipped sin tocar la DB}';

    protected $description = 'Marca skipped las cleaning tasks huérfanas: reservas canceladas o ya checked_out que no fueron skipeadas en su momento.';

    /**
     * Gracia antes de considerar una reserva "confirmed" con checkout pasado
     * como huérfana. Cubre lags de sincronización con Hostex.
     */
    private const STALE_CONFIRMED_GRACE_DAYS = 2;

    public function handle(): int
    {
        $cancelledTaskIds = CleaningTask::query()
            ->active()
            ->whereHas('reservation', fn ($q) => $q->where('status', ReservationStatus::Cancelled))
            ->pluck('id');

        $supersededTaskIds = $this->findSupersededTaskIds();

        $staleConfirmedTaskIds = CleaningTask::query()
            ->active()
            ->whereHas('reservation', fn ($q) => $q
                ->where('status', ReservationStatus::Confirmed)
                ->whereDate('check_out', '<', today()->subDays(self::STALE_CONFIRMED_GRACE_DAYS))
            )
            ->pluck('id');

        $allIds = $cancelledTaskIds
            ->merge($supersededTaskIds)
            ->merge($staleConfirmedTaskIds)
            ->unique();
        $total = $allIds->count();

        $this->info("Encontradas {$cancelledTaskIds->count()} tasks de reservas canceladas.");
        $this->info("Encontradas {$supersededTaskIds->count()} tasks superadas por estancia posterior.");
        $this->info("Encontradas {$staleConfirmedTaskIds->count()} tasks de reservas confirmed con checkout anterior a hoy-".self::STALE_CONFIRMED_GRACE_DAYS.' días.');
        $this->info("Total único: {$total}.");

        if ($total === 0) {
            $this->info('Nada para hacer.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run — no se actualiza nada.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($allIds): void {
            CleaningTask::query()
                ->whereIn('id', $allIds)
                ->update(['status' => CleaningTaskStatus::Skipped]);
        });

        $this->info("Marcadas {$total} tasks como skipped.");

        return self::SUCCESS;
    }

    /**
     * Tasks cuya reserva ya hizo checkout pero hay otra reserva posterior en la
     * misma propiedad que ya llegó (checked_in / checked_out). Significa que
     * otro huésped entró después, así que la limpieza pendiente quedó obsoleta.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function findSupersededTaskIds(): \Illuminate\Support\Collection
    {
        return CleaningTask::query()
            ->active()
            ->whereHas('reservation', fn ($q) => $q->where('status', ReservationStatus::CheckedOut))
            ->with('reservation:id,check_in,property_id')
            ->get()
            ->filter(function (CleaningTask $task): bool {
                return Reservation::query()
                    ->where('property_id', $task->property_id)
                    ->whereDate('check_in', '>', $task->scheduled_date)
                    ->whereIn('status', [
                        ReservationStatus::CheckedIn->value,
                        ReservationStatus::CheckedOut->value,
                    ])
                    ->exists();
            })
            ->pluck('id');
    }
}
