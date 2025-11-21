<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TbtFormationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-calculate month from week_start_date if not provided
        if ($this->has('week_start_date') && !$this->has('month')) {
            $startDate = \Carbon\Carbon::parse($this->input('week_start_date'));
            $this->merge([
                'month' => $startDate->month,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'participant' => [
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'required',
                Rule::in(['planned', 'realized']),
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
            ],
            'week_start_date' => [
                'required',
                'date',
            ],
            'week_end_date' => [
                'required',
                'date',
                'after_or_equal:week_start_date',
            ],
            'month' => [
                'required',
                'integer',
                'min:1',
                'max:12',
            ],
            'notes' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'boolean',
            ],
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
            'year.required' => 'L\'année est requise.',
            'week_start_date.required' => 'La date de début de semaine est requise.',
            'week_end_date.required' => 'La date de fin de semaine est requise.',
            'week_end_date.after_or_equal' => 'La date de fin doit être supérieure ou égale à la date de début.',
            'month.required' => 'Le mois est requis.',
            'month.min' => 'Le mois doit être entre 1 et 12.',
            'month.max' => 'Le mois doit être entre 1 et 12.',
            'status.required' => 'Le statut est requis.',
        ];
    }
}
