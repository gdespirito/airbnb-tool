<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Http\Requests\Reservations\StoreReservationRequest;
use App\Http\Requests\Reservations\UpdateReservationRequest;
use App\Models\Property;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    public function index(): Response
    {
        $reservations = Reservation::query()
            ->with('property')
            ->upcoming()
            ->get()
            ->map(fn ($r) => [
                ...$r->toArray(),
                'is_same_day_turnover' => $r->isSameDayTurnover(),
            ]);

        $properties = Property::all(['id', 'name', 'slug']);

        return Inertia::render('reservations/Index', [
            'reservations' => $reservations,
            'properties' => $properties,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('reservations/Create', [
            'properties' => Property::all(['id', 'name', 'slug']),
            'statuses' => ReservationStatus::cases(),
        ]);
    }

    public function store(StoreReservationRequest $request): RedirectResponse
    {
        $reservation = Reservation::create($request->validated());

        return to_route('reservations.show', $reservation)->with('status', 'Reservation created.');
    }

    public function show(Reservation $reservation): Response
    {
        $reservation->load(['property', 'reservationNotes']);

        return Inertia::render('reservations/Show', [
            'reservation' => $reservation,
            'isSameDayTurnover' => $reservation->isSameDayTurnover(),
        ]);
    }

    public function edit(Reservation $reservation): Response
    {
        $reservation->load('property');

        return Inertia::render('reservations/Edit', [
            'reservation' => $reservation,
            'properties' => Property::all(['id', 'name', 'slug']),
            'statuses' => ReservationStatus::cases(),
        ]);
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        $reservation->update($request->validated());

        return to_route('reservations.show', $reservation)->with('status', 'Reservation updated.');
    }

    public function destroy(Reservation $reservation): RedirectResponse
    {
        $reservation->delete();

        return to_route('reservations.index')->with('status', 'Reservation deleted.');
    }
}
