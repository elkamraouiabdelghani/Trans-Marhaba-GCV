<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DriverViolationRequest extends FormRequest
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
            'driver_id' => ['required', 'exists:drivers,id'],
            'flotte_id' => ['nullable', 'exists:flottes,id'],
            'violation_type_id' => ['required', 'exists:violation_types,id'],
            'violation_date' => ['required', 'date'],
            'violation_time' => ['nullable', 'date_format:H:i:s'],
            'speed' => ['nullable', 'numeric', 'min:0'],
            'speed_limit' => ['nullable', 'numeric', 'min:0'],
            'violation_duration_seconds' => ['nullable', 'integer', 'min:0'],
            'violation_distance_km' => ['nullable', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'location_lat' => ['nullable', 'numeric'],
            'location_lng' => ['nullable', 'numeric'],
            'status' => ['nullable', 'in:pending,rejected,confirmed'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'description' => ['nullable', 'string'],
            'document' => ['nullable', 'array'],
            'document.*' => ['file', 'max:10240'],
            'analysis' => ['nullable', 'string'],
            'action_plan' => ['nullable', 'string'],
            'evidence' => ['nullable', 'file', 'max:10240'],
        ];
    }
}
