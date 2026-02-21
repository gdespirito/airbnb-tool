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
        $reply = ReservationNote::create([
            'reservation_id' => $reservationNote->reservation_id,
            'parent_id' => $reservationNote->id,
            'content' => $request->validated('content'),
        ]);

        $reservationNote->responded_at = now();
        $reservationNote->save();

        $reply->load('reservation.property');

        NotifyAgentResponse::dispatch($reply);

        return back()->with('status', 'Response sent.');
    }
}
