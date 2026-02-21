<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReservationRequest;
use App\Http\Requests\Api\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ReservationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'status' => ['nullable', 'string'],
            'check_in_from' => ['nullable', 'date'],
            'check_in_to' => ['nullable', 'date', 'after_or_equal:check_in_from'],
            'check_out_from' => ['nullable', 'date'],
            'check_out_to' => ['nullable', 'date'],
            'upcoming' => ['nullable', 'in:0,1,true,false'],
            'current' => ['nullable', 'in:0,1,true,false'],
        ]);

        $query = Reservation::query()->with('property');

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->integer('property_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('check_in_from')) {
            $query->whereDate('check_in', '>=', $request->string('check_in_from'));
        }

        if ($request->filled('check_in_to')) {
            $query->whereDate('check_in', '<=', $request->string('check_in_to'));
        }

        if ($request->filled('check_out_from')) {
            $query->whereDate('check_out', '>=', $request->string('check_out_from'));
        }

        if ($request->filled('check_out_to')) {
            $query->whereDate('check_out', '<=', $request->string('check_out_to'));
        }

        if ($request->boolean('current')) {
            $query->whereDate('check_in', '<=', now())
                ->whereDate('check_out', '>=', now())
                ->whereNot('status', \App\Enums\ReservationStatus::Cancelled);
        }

        if ($request->boolean('upcoming')) {
            $query->upcoming();
        }

        return ReservationResource::collection($query->get());
    }

    public function store(StoreReservationRequest $request): ReservationResource
    {
        $reservation = Reservation::create($request->validated());
        $reservation->load('property');

        return new ReservationResource($reservation);
    }

    public function show(Reservation $reservation): ReservationResource
    {
        $reservation->load('property');

        return new ReservationResource($reservation);
    }

    public function update(UpdateReservationRequest $request, Reservation $reservation): ReservationResource
    {
        $reservation->update($request->validated());
        $reservation->load('property');

        return new ReservationResource($reservation);
    }

    public function destroy(Reservation $reservation): Response
    {
        $reservation->delete();

        return response()->noContent();
    }
}
