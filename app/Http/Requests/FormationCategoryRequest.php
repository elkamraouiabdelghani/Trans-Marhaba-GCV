<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FormationCategoryRequest extends FormRequest
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
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper($this->input('code')),
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
        $category = $this->route('formation_category') ?? $this->route('formationCategory');
        $categoryId = $category?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('formation_categories', 'name')->ignore($categoryId),
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('formation_categories', 'code')->ignore($categoryId),
            ],
        ];
    }
}

