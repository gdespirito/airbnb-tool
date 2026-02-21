<?php

namespace App\Http\Requests\Api;

use App\Enums\ReservationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReservationRequest extends FormRequest
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
            'property_id' => ['sometimes', 'integer', 'exists:properties,id'],
            'guest_name' => ['sometimes', 'string', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:50'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'number_of_guests' => ['nullable', 'integer', 'min:1'],
            'check_in' => ['sometimes', 'date'],
            'check_out' => ['sometimes', 'date', 'after:check_in'],
            'status' => ['sometimes', Rule::enum(ReservationStatus::class)],
            'notes' => ['nullable', 'string'],
            'source' => ['sometimes', 'string', 'max:50'],
            'airbnb_reservation_id' => ['nullable', 'string', 'max:255', Rule::unique('reservations', 'airbnb_reservation_id')->ignore($this->reservation)],
            'check_in_time' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'check_out_time' => ['nullable', 'string', 'regex:/^\d{2}:\d{2}$/'],
        ];
    }
}
