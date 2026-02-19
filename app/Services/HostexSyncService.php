<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Property;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class HostexSyncService
{
    private const STATUS_MAP = [
        'accepted' => ReservationStatus::Confirmed,
        'cancelled' => ReservationStatus::Cancelled,
        'denied' => ReservationStatus::Cancelled,
        'timeout' => ReservationStatus::Cancelled,
        'wait_accept' => ReservationStatus::Confirmed,
        'wait_pay' => ReservationStatus::Confirmed,
    ];

    /**
     * @param  array<string, mixed>  $data  Raw Hostex reservation data
     */
    public function upsertFromHostexData(array $data): ?Reservation
    {
        $hostexPropertyId = $data['property_id'] ?? null;
        $reservationCode = $data['reservation_code'] ?? null;

        if (! $hostexPropertyId || ! $reservationCode) {
            Log::warning('Hostex sync: missing property_id or reservation_code', ['data' => $data]);

            return null;
        }

        $property = Property::query()->where('hostex_property_id', $hostexPropertyId)->first();

        if (! $property) {
            Log::warning('Hostex sync: no matching property found', [
                'hostex_property_id' => $hostexPropertyId,
            ]);

            return null;
        }

        $status = self::STATUS_MAP[$data['status'] ?? ''] ?? ReservationStatus::Confirmed;

        $attributes = [
            'property_id' => $property->id,
            'guest_name' => $data['guest_name'] ?? null,
            'guest_phone' => $data['guest_phone'] ?? null,
            'guest_email' => $data['guest_email'] ?? null,
            'number_of_guests' => $data['number_of_guests'] ?? null,
            'number_of_adults' => $data['number_of_adults'] ?? null,
            'number_of_children' => $data['number_of_children'] ?? null,
            'number_of_infants' => $data['number_of_infants'] ?? null,
            'number_of_pets' => $data['number_of_pets'] ?? null,
            'check_in' => $data['check_in'] ?? null,
            'check_out' => $data['check_out'] ?? null,
            'status' => $status,
            'source' => 'hostex',
            'channel_type' => $data['channel_type'] ?? null,
            'booked_at' => $data['booked_at'] ?? null,
            'cancelled_at' => $data['cancelled_at'] ?? null,
            'total_price' => $data['rates']['total_rate']['amount'] ?? null,
            'currency' => $data['rates']['total_rate']['currency'] ?? null,
            'check_in_time' => $data['check_in_details']['arrival_at'] ?? null,
            'check_out_time' => $data['check_in_details']['departure_at'] ?? null,
            'lock_code' => $data['check_in_details']['lock_code'] ?? null,
            'hostex_conversation_id' => $data['conversation_id'] ?? null,
        ];

        $metadata = array_filter([
            'hostex_tags' => $data['tags'] ?? null,
            'hostex_custom_fields' => $data['custom_fields'] ?? null,
            'hostex_rate_details' => $data['rates'] ?? null,
        ]);

        if ($metadata) {
            $attributes['metadata'] = $metadata;
        }

        // Restore soft-deleted reservation if it exists
        $existing = Reservation::withTrashed()
            ->where('airbnb_reservation_id', $reservationCode)
            ->first();

        if ($existing?->trashed()) {
            $existing->restore();
        }

        return Reservation::updateOrCreate(
            ['airbnb_reservation_id' => $reservationCode],
            $attributes,
        );
    }
}
