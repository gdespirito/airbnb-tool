<x-mail::message>
# Limpieza completada

**Propiedad:** {{ $task->property?->name ?? 'N/A' }}

**Limpiador(a):** {{ $task->assigned_to ?? 'N/A' }}

**Fecha:** {{ $task->scheduled_date?->format('d/m/Y') }}

@if($task->estimated_arrival_time)
**Hora de llegada:** {{ $task->estimated_arrival_time }}
@endif

**Hora de completado:** {{ $task->completed_at?->format('H:i') }}

@if($hasSameDayCheckin)
> **Check-in el mismo día** — El siguiente huésped ({{ $nextGuestName }}) llega hoy. La limpieza fue completada a tiempo.
@endif

@if($task->notes)
---

**Notas:** {{ $task->notes }}
@endif

@if($task->photos->count() > 0)
---

**Fotos adjuntas:** {{ $task->photos->count() }} imagen(es)
@endif

<x-mail::button :url="config('app.url') . '/cleaning-tasks/' . $task->id">
Ver tarea
</x-mail::button>
</x-mail::message>
