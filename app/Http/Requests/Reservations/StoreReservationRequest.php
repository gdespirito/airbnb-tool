<?php

namespace App\Http\Requests\Reservations;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
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
            'guest_phone' => ['nullable', 'string', 'max:30'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'number_of_guests' => ['required', 'integer', 'min:1', 'max:20'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'status' => ['required', Rule::enum(ReservationStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'airbnb_reservation_id' => ['nullable', 'string', 'max:100', 'unique:reservations,airbnb_reservation_id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled(['property_id', 'check_in', 'check_out'])) {
                $overlap = Reservation::query()
                    ->where('property_id', $this->property_id)
                    ->where('status', '!=', ReservationStatus::Cancelled->value)
                    ->where('check_in', '<', $this->check_out)
                    ->where('check_out', '>', $this->check_in)
                    ->exists();

                if ($overlap) {
                    $validator->errors()->add('check_in', 'These dates overlap with an existing reservation for this property.');
                }
            }
        });
    }
}
