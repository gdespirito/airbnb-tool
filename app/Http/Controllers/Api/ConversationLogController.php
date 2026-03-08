<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreConversationLogRequest;
use App\Http\Resources\ConversationLogResource;
use App\Models\ConversationLog;
use App\Models\Reservation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ConversationLogController extends Controller
{
    public function index(Reservation $reservation): AnonymousResourceCollection
    {
        return ConversationLogResource::collection(
            $reservation->conversationLogs()->latest()->get()
        );
    }

    public function store(StoreConversationLogRequest $request, Reservation $reservation): ConversationLogResource
    {
        $log = $reservation->conversationLogs()->create($request->validated());

        return new ConversationLogResource($log->refresh());
    }

    public function show(ConversationLog $conversationLog): ConversationLogResource
    {
        return new ConversationLogResource($conversationLog);
    }
}
