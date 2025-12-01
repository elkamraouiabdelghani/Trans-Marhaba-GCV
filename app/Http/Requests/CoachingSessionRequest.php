<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoachingSessionRequest extends FormRequest
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
            'driver_id' => ['required', 'exists:drivers,id'],
            'flotte_id' => ['nullable', 'exists:flottes,id'],
            'date' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date'],
            'type' => ['required', 'in:initial,suivi,correctif,route_analysing,obc_suite,other'],
            'route_taken' => ['nullable', 'string', 'max:255'],
            'moniteur' => ['nullable', 'string', 'max:255'],
            'assessment' => ['nullable', 'string'],
            'status' => ['required', 'in:planned,in_progress,completed,cancelled'],
            'validity_days' => ['required', 'integer', 'min:1'],
            'next_planning_session' => ['nullable', 'date'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }
}


