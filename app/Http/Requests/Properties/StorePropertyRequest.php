<?php

namespace App\Http\Requests\Properties;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StorePropertyRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'airbnb_url' => ['nullable', 'url', 'max:500'],
            'airbnb_listing_id' => ['nullable', 'string', 'max:100'],
            'ical_url' => ['nullable', 'url', 'max:500'],
            'location' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'checkin_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'checkout_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
            'cleaning_contact_id' => ['nullable', 'exists:contacts,id'],
        ];
    }

    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if (is_array($data)) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $data;
    }
}
