<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'cin' => 'nullable|string|max:50',
            'visite_medical' => 'nullable|date',
            'visite_yeux' => 'nullable|date',
            'formation_imd' => 'nullable|date',
            'formation_16_module' => 'nullable|date',
            'date_integration' => 'nullable|date',
            'attestation_travail' => 'nullable|string',
            'carte_profession' => 'nullable|string',
            'n_cnss' => 'nullable|string|max:50',
            'rib' => 'nullable|string|max:50',
            'license_number' => 'required|string|max:50',
            'license_type' => 'nullable|string|max:50',
            'license_issue_date' => 'nullable|date',
            'license_class' => 'nullable|string|max:50',
            'status' => 'nullable|string',
            'assigned_vehicle_id' => 'nullable|exists:vehicles,id',
            'flotte_id' => 'nullable|exists:flottes,id',
            'notes' => 'nullable|string',
            'documents' => 'nullable|array',
            'documents.*' => 'file|max:10240', // 10MB max per file
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
            'full_name.required' => 'The full name field is required.',
            'full_name.string' => 'The full name must be a string.',
            'full_name.max' => 'The full name may not be greater than 255 characters.',
            'email.email' => 'The email must be a valid email address.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'phone.max' => 'The phone number may not be greater than 20 characters.',
            'city.max' => 'The city may not be greater than 255 characters.',
            'cin.max' => 'The CIN may not be greater than 50 characters.',
            'date_of_birth.date' => 'The date of birth must be a valid date.',
            'visite_medical.date' => 'The medical visit date must be a valid date.',
            'visite_yeux.date' => 'The eye visit date must be a valid date.',
            'formation_imd.date' => 'The IMD formation date must be a valid date.',
            'formation_16_module.date' => 'The 16 module formation date must be a valid date.',
            'date_integration.date' => 'The integration date must be a valid date.',
            'n_cnss.max' => 'The CNSS number may not be greater than 50 characters.',
            'rib.max' => 'The RIB may not be greater than 50 characters.',
            'license_number.required' => 'The license number field is required.',
            'license_number.string' => 'The license number must be a string.',
            'license_number.max' => 'The license number may not be greater than 50 characters.',
            'license_type.max' => 'The license type may not be greater than 50 characters.',
            'license_issue_date.date' => 'The license issue date must be a valid date.',
            'license_class.max' => 'The license class may not be greater than 50 characters.',
            'assigned_vehicle_id.exists' => 'The selected vehicle does not exist.',
            'flotte_id.exists' => 'The selected flotte does not exist.',
            'documents.array' => 'The documents must be an array.',
            'documents.*.file' => 'Each document must be a valid file.',
            'documents.*.max' => 'Each document may not be greater than 10MB.',
        ];
    }
}

