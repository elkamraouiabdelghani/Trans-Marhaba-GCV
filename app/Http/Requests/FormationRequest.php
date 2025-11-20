<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormationRequest extends FormRequest
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
        $formationId = $this->route('formation')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('formations', 'name')->ignore($formationId),
            ],
            'participant' => ['nullable', 'string'],
            'theme' => ['nullable', 'string', 'max:255'],
            'duree' => ['nullable', 'integer', 'min:0'],
            'realizing_date' => ['nullable', 'date'],
            'status' => ['required', 'in:planned,realized'],
            'organisme' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'formation_category_id' => ['nullable', 'exists:formation_categories,id'],
            'flotte_id' => ['nullable', 'exists:flottes,id'],
            'delivery_type' => ['required', 'in:interne,externe'],
            'is_active' => ['sometimes', 'boolean'],
            'obligatoire' => ['sometimes', 'boolean'],
            'reference_value' => ['nullable', 'integer', 'min:1'],
            'reference_unit' => ['nullable', 'in:months,years'],
            'warning_alert_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'warning_alert_days' => ['nullable', 'integer', 'min:0'],
            'critical_alert_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'critical_alert_days' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->filled('status')) {
            $this->merge(['status' => 'planned']);
        }
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        $validated['is_active'] = $this->boolean('is_active');
        $validated['obligatoire'] = $this->boolean('obligatoire');
        $validated['status'] = $validated['status'] ?? 'planned';

        return $validated;
    }
}

