<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterviewAnswersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        $questions = config('turnover_interview.questions', []);

        foreach ($questions as $question) {
            $key = $question['key'];
            $type = $question['type'] ?? 'text';

            if ($type === 'rating') {
                $rules[$key] = ['nullable', 'integer', 'between:1,5'];
            } else {
                $rules[$key] = ['nullable', 'string', 'max:2000'];
            }
        }

        $rules['employee_name'] = ['nullable', 'string', 'max:255'];
        $rules['interview_date'] = ['nullable', 'date'];
        $rules['employee_signature'] = ['nullable', 'string', 'max:1000'];

        return $rules;
    }
}

