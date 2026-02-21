<?php

namespace App\Http\Controllers;

use App\Http\Requests\Properties\StorePropertyRequest;
use App\Http\Requests\Properties\UpdatePropertyRequest;
use App\Models\Contact;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    public function index(): Response
    {
        $properties = Property::query()
            ->with('cleaningContact')
            ->withCount(['reservations as upcoming_reservations_count' => function ($query) {
                $query->whereDate('check_in', '>=', now());
            }])
            ->get();

        return Inertia::render('properties/Index', [
            'properties' => $properties,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('properties/Create', [
            'contacts' => Contact::query()->where('role', 'cleaning')->get(['id', 'name']),
        ]);
    }

    public function store(StorePropertyRequest $request): RedirectResponse
    {
        $property = Property::create($request->validated());

        return to_route('properties.show', $property)->with('status', 'Property created.');
    }

    public function show(Property $property): Response
    {
        $property->load([
            'cleaningContact',
            'reservations' => fn ($q) => $q->upcoming()->with('property')->limit(10),
        ]);

        $todayCheckouts = $property->reservations()
            ->whereDate('check_out', today())
            ->get();

        $todayCheckins = $property->reservations()
            ->whereDate('check_in', today())
            ->get();

        return Inertia::render('properties/Show', [
            'property' => $property,
            'todayCheckouts' => $todayCheckouts,
            'todayCheckins' => $todayCheckins,
        ]);
    }

    public function edit(Property $property): Response
    {
        return Inertia::render('properties/Edit', [
            'property' => $property,
            'contacts' => Contact::query()->where('role', 'cleaning')->get(['id', 'name']),
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property): RedirectResponse
    {
        $property->update($request->validated());

        return to_route('properties.show', $property)->with('status', 'Property updated.');
    }
}
