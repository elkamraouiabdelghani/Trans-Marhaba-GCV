<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestPointRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:area,station,parking,other'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('messages.name_required') ?? 'The name field is required.',
            'type.required' => __('messages.type_required') ?? 'The type field is required.',
            'type.in' => __('messages.type_invalid') ?? 'The selected type is invalid.',
            'latitude.required' => __('messages.latitude_required') ?? 'Please select a location on the map.',
            'latitude.numeric' => __('messages.latitude_numeric') ?? 'Latitude must be a number.',
            'latitude.between' => __('messages.latitude_between') ?? 'Latitude must be between -90 and 90.',
            'longitude.required' => __('messages.longitude_required') ?? 'Please select a location on the map.',
            'longitude.numeric' => __('messages.longitude_numeric') ?? 'Longitude must be a number.',
            'longitude.between' => __('messages.longitude_between') ?? 'Longitude must be between -180 and 180.',
        ];
    }
}

