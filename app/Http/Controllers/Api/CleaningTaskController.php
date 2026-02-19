<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateCleaningTaskStatusRequest;
use App\Http\Resources\CleaningTaskResource;
use App\Models\CleaningTask;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CleaningTaskController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'status' => ['nullable', 'string'],
            'upcoming' => ['nullable', 'boolean'],
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

    public function show(CleaningTask $cleaningTask): CleaningTaskResource
    {
        $cleaningTask->load(['property', 'reservation', 'contact']);

        return new CleaningTaskResource($cleaningTask);
    }

    public function updateStatus(UpdateCleaningTaskStatusRequest $request, CleaningTask $cleaningTask): CleaningTaskResource
    {
        $cleaningTask->update($request->validated());
        $cleaningTask->load(['property', 'reservation', 'contact']);

        return new CleaningTaskResource($cleaningTask);
    }
}
