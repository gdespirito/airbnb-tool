<?php

namespace App\Http\Requests\CleaningTasks;

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
            'reservation_id' => ['nullable', 'integer', 'exists:reservations,id'],
            'status' => ['required', Rule::enum(CleaningTaskStatus::class)],
            'cleaning_type' => ['required', Rule::enum(CleaningType::class)],
            'cleaning_fee' => ['nullable', 'integer', 'min:0'],
            'scheduled_date' => ['required', 'date'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'assigned_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
