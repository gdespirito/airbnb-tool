<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreConversationLogRequest extends FormRequest
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
            'from_agent' => ['required', 'string', 'in:alma,tita,atlas'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_role' => ['required', 'string', 'in:guest,cleaner,maintenance,owner'],
            'channel' => ['sometimes', 'string', 'in:whatsapp,phone'],
            'summary' => ['required', 'string'],
            'messages' => ['nullable', 'array'],
            'messages.*.from' => ['required_with:messages', 'string'],
            'messages.*.text' => ['required_with:messages', 'string'],
            'messages.*.at' => ['required_with:messages', 'string'],
        ];
    }
}
