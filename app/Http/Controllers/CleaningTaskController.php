<?php

namespace App\Http\Controllers;

use App\Enums\CleaningTaskStatus;
use App\Enums\CleaningType;
use App\Http\Requests\CleaningTasks\StoreCleaningTaskRequest;
use App\Http\Requests\CleaningTasks\UpdateCleaningTaskRequest;
use App\Models\CleaningTask;
use App\Models\Contact;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CleaningTaskController extends Controller
{
    public function index(): Response
    {
        $cleaningTasks = CleaningTask::query()
            ->with(['property', 'reservation', 'contact'])
            ->upcoming()
            ->get();

        return Inertia::render('cleaning-tasks/Index', [
            'cleaningTasks' => $cleaningTasks,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('cleaning-tasks/Create', [
            'properties' => Property::all(['id', 'name', 'slug']),
            'contacts' => Contact::query()->orderBy('name')->get(['id', 'name', 'role']),
            'statuses' => CleaningTaskStatus::cases(),
            'cleaningTypes' => CleaningType::cases(),
        ]);
    }

    public function store(StoreCleaningTaskRequest $request): RedirectResponse
    {
        $cleaningTask = CleaningTask::create($request->validated());

        return to_route('cleaning-tasks.show', $cleaningTask)->with('status', 'Cleaning task created.');
    }

    public function show(CleaningTask $cleaningTask): Response
    {
        $cleaningTask->load(['property', 'reservation', 'contact']);

        return Inertia::render('cleaning-tasks/Show', [
            'cleaningTask' => $cleaningTask,
        ]);
    }

    public function edit(CleaningTask $cleaningTask): Response
    {
        $cleaningTask->load(['property', 'reservation', 'contact']);

        return Inertia::render('cleaning-tasks/Edit', [
            'cleaningTask' => $cleaningTask,
            'properties' => Property::all(['id', 'name', 'slug']),
            'contacts' => Contact::query()->orderBy('name')->get(['id', 'name', 'role']),
            'statuses' => CleaningTaskStatus::cases(),
            'cleaningTypes' => CleaningType::cases(),
        ]);
    }

    public function update(UpdateCleaningTaskRequest $request, CleaningTask $cleaningTask): RedirectResponse
    {
        $cleaningTask->update($request->validated());

        return to_route('cleaning-tasks.show', $cleaningTask)->with('status', 'Cleaning task updated.');
    }

    public function destroy(CleaningTask $cleaningTask): RedirectResponse
    {
        $cleaningTask->delete();

        return to_route('cleaning-tasks.index')->with('status', 'Cleaning task deleted.');
    }
}
