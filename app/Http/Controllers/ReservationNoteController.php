<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationNotes\RespondReservationNoteRequest;
use App\Jobs\NotifyAgentResponse;
use App\Models\ReservationNote;
use Illuminate\Http\RedirectResponse;

class ReservationNoteController extends Controller
{
    public function respond(RespondReservationNoteRequest $request, ReservationNote $reservationNote): RedirectResponse
    {
        $reservationNote->update([
            'content' => $request->validated('content'),
        ]);

        $reservationNote->responded_at = now();
        $reservationNote->save();

        $reservationNote->load('reservation.property');

        NotifyAgentResponse::dispatch($reservationNote);

        return back()->with('status', 'Response sent.');
    }
}
