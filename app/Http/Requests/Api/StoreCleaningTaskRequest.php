<?php

namespace App\Http\Requests\Api;

use App\Enums\CleaningTaskStatus;
use App\Enums\CleaningType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCleaningTaskRequest extends FormRequest
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
            'reservation_id' => [
                'nullable',
                'integer',
                Rule::exists('reservations', 'id')
                    ->where('property_id', $this->input('property_id')),
            ],
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'status' => ['nullable', Rule::enum(CleaningTaskStatus::class)],
            'cleaning_type' => ['nullable', Rule::enum(CleaningType::class)],
            'cleaning_fee' => ['nullable', 'integer', 'min:0'],
            'scheduled_date' => ['required', 'date'],
            'planned_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
