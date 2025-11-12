<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTurnoverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // All authenticated users can create/update turnovers
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'departure_date' => ['required', 'date'],
            'flotte' => ['nullable', 'string', 'max:255'],
            'driver_id' => ['nullable', 'exists:drivers,id', 'required_without:user_id'],
            'user_id' => ['nullable', 'exists:users,id', 'required_without:driver_id'],
            'departure_reason' => ['required', 'string', 'max:65535'],
            'interview_notes' => ['nullable', 'string', 'max:65535'],
            'interviewed_by' => ['nullable', 'string', 'max:255'],
            'observations' => ['nullable', 'string', 'max:65535'],
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
            'driver_id.required_without' => 'Either a driver or an administration staff member must be selected.',
            'user_id.required_without' => 'Either a driver or an administration staff member must be selected.',
        ];
    }
}
