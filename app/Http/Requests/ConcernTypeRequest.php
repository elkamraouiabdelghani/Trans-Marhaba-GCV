<?php

namespace App\Http\Requests;

use App\Models\ConcernType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConcernTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->input('status', 'medium'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $concernType = $this->route('concern_type');
        $ignoreId = is_object($concernType) ? $concernType->getKey() : $concernType;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('concern_types', 'name')->ignore($ignoreId),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(ConcernType::STATUSES)],
        ];
    }
}
