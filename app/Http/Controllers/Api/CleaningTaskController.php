<?php

namespace App\Http\Controllers\Api;

use App\Enums\CleaningTaskStatus;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCleaningTaskPhotoRequest;
use App\Http\Requests\Api\UpdateCleaningTaskRequest;
use App\Http\Requests\Api\UpdateCleaningTaskStatusRequest;
use App\Http\Resources\CleaningTaskResource;
use App\Mail\CleaningTaskCompleted;
use App\Models\CleaningTask;
use App\Models\CleaningTaskPhoto;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Mail;

class CleaningTaskController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'status' => ['nullable', 'string'],
            'upcoming' => ['nullable', 'in:0,1,true,false'],
        ]);

        $query = CleaningTask::query()->with(['property', 'reservation', 'contact']);

        if ($request->filled('property_id')) {
            $query->forProperty($request->integer('property_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->boolean('upcoming')) {
            $query->upcoming();
        }

        return CleaningTaskResource::collection($query->get());
    }

    public function today(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
        ]);

        $query = CleaningTask::query()
            ->with(['property', 'reservation', 'contact', 'photos'])
            ->forToday();

        if ($request->filled('property_id')) {
            $query->forProperty($request->integer('property_id'));
        }

        $tasks = $query->get();

        return CleaningTaskResource::collection($tasks->map(function (CleaningTask $task) {
            $nextReservation = Reservation::query()
                ->where('property_id', $task->property_id)
                ->whereDate('check_in', $task->scheduled_date)
                ->whereNot('status', ReservationStatus::Cancelled)
                ->first();

            $task->setAttribute('has_same_day_checkin', $nextReservation !== null);
            $task->setAttribute('next_guest_name', $nextReservation?->guest_name);
            $task->setAttribute('checkin_time', $task->property?->checkin_time);

            return $task;
        }))->additional([
            'meta' => ['date' => today()->toDateString()],
        ]);
    }

    public function show(CleaningTask $cleaningTask): CleaningTaskResource
    {
        $cleaningTask->load(['property', 'reservation', 'contact', 'photos']);

        return new CleaningTaskResource($cleaningTask);
    }

    public function update(UpdateCleaningTaskRequest $request, CleaningTask $cleaningTask): CleaningTaskResource
    {
        $data = $request->validated();

        if (
            isset($data['status'])
            && $data['status'] === CleaningTaskStatus::InProgress->value
            && $cleaningTask->started_at === null
        ) {
            $data['started_at'] = now();
        }

        $cleaningTask->update($data);
        $cleaningTask->load(['property', 'reservation', 'contact', 'photos']);

        return new CleaningTaskResource($cleaningTask);
    }

    public function updateStatus(UpdateCleaningTaskStatusRequest $request, CleaningTask $cleaningTask): CleaningTaskResource
    {
        $cleaningTask->update($request->validated());
        $cleaningTask->load(['property', 'reservation', 'contact']);

        return new CleaningTaskResource($cleaningTask);
    }

    public function storePhotos(StoreCleaningTaskPhotoRequest $request, CleaningTask $cleaningTask): CleaningTaskResource
    {
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store("cleaning-photos/{$cleaningTask->id}", 's3');

            CleaningTaskPhoto::create([
                'cleaning_task_id' => $cleaningTask->id,
                'file_path' => $path,
                'disk' => 's3',
                'original_filename' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getMimeType(),
                'file_size' => $photo->getSize(),
            ]);
        }

        $cleaningTask->load(['property', 'reservation', 'contact', 'photos']);

        return new CleaningTaskResource($cleaningTask);
    }

    public function complete(Request $request, CleaningTask $cleaningTask): CleaningTaskResource
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $data = [
            'status' => CleaningTaskStatus::Completed,
            'completed_at' => now(),
        ];

        if ($request->filled('notes')) {
            $data['notes'] = $request->input('notes');
        }

        $cleaningTask->update($data);
        $cleaningTask->load(['property', 'reservation', 'contact', 'photos']);

        Mail::to(User::all())->send(new CleaningTaskCompleted($cleaningTask));

        return new CleaningTaskResource($cleaningTask);
    }
}
