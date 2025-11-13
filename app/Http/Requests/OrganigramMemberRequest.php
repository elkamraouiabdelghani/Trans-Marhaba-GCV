<?php

namespace App\Http\Requests;

use App\Models\OrganigramMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrganigramMemberRequest extends FormRequest
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
            'position' => [
                'required',
                'string',
                Rule::in(OrganigramMember::POSITIONS),
                Rule::unique('organigram_members', 'position')->ignore($this->route('organigram')),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('position')) {
            $this->merge([
                'position' => strtoupper(str_replace(' ', '_', $this->input('position'))),
            ]);
        }
    }
}

