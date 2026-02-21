<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReservationNoteRequest;
use App\Http\Requests\Api\UpdateReservationNoteRequest;
use App\Http\Resources\ReservationNoteResource;
use App\Jobs\NotifyAgentResponse;
use App\Mail\ReservationNoteCreated;
use App\Models\Reservation;
use App\Models\ReservationNote;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class ReservationNoteController extends Controller
{
    public function index(Reservation $reservation): AnonymousResourceCollection
    {
        return ReservationNoteResource::collection(
            $reservation->reservationNotes()->latest()->get()
        );
    }

    public function store(StoreReservationNoteRequest $request, Reservation $reservation): ReservationNoteResource
    {
        $note = $reservation->reservationNotes()->create($request->validated());

        $note->load('reservation.property');

        Mail::to(User::all())->send(new ReservationNoteCreated($note));

        return new ReservationNoteResource($note);
    }

    public function show(ReservationNote $reservationNote): ReservationNoteResource
    {
        return new ReservationNoteResource($reservationNote);
    }

    public function update(UpdateReservationNoteRequest $request, ReservationNote $reservationNote): ReservationNoteResource
    {
        $shouldNotifyAgent = $reservationNote->needs_response
            && $reservationNote->responded_at === null
            && $request->has('content');

        $reservationNote->update($request->validated());

        if ($shouldNotifyAgent) {
            $reservationNote->responded_at = now();
            $reservationNote->save();
            $reservationNote->load('reservation.property');
            NotifyAgentResponse::dispatch($reservationNote);
        }

        return new ReservationNoteResource($reservationNote->fresh());
    }

    public function destroy(ReservationNote $reservationNote): Response
    {
        $reservationNote->delete();

        return response()->noContent();
    }
}
