<?php

namespace App\Http\Requests\Api;

use App\Enums\ReservationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:50'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'number_of_guests' => ['nullable', 'integer', 'min:1'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'status' => ['nullable', Rule::enum(ReservationStatus::class)],
            'notes' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'max:50'],
            'airbnb_reservation_id' => ['nullable', 'string', 'max:255', 'unique:reservations,airbnb_reservation_id'],
        ];
    }
}
