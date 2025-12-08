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
            'from_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'from_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'from_location_name' => ['nullable', 'string', 'max:255'],
            'to_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'to_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'to_location_name' => ['nullable', 'string', 'max:255'],
            'rest_places' => ['nullable', 'array'],
            'rest_places.*' => ['nullable', 'string', 'max:255'],
            'moniteur' => ['nullable', 'string', 'max:255'],
            'assessment' => ['nullable', 'string'],
            'status' => ['required', 'in:planned,in_progress,completed,cancelled'],
            'validity_days' => ['required', 'integer', 'min:1'],
            'next_planning_session' => ['nullable', 'date'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $validityDays = $this->input('validity_days');
            $restPlaces = $this->input('rest_places', []);
            
            // Only validate if rest_places is provided and validity_days exists
            if ($validityDays && is_array($restPlaces) && !empty($restPlaces)) {
                $maxCount = $validityDays - 1;
                $actualCount = count(array_filter($restPlaces)); // Count non-empty values
                
                // Allow up to validity_days - 1 rest places (0 to max)
                if ($actualCount > $maxCount) {
                    $validator->errors()->add(
                        'rest_places',
                        __('messages.rest_places_max_exceeded', ['max' => $maxCount, 'actual' => $actualCount])
                    );
                }
            }
        });
    }
}


