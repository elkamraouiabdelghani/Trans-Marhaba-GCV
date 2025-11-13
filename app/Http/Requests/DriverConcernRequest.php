<?php

namespace App\Http\Requests;

use App\Models\DriverConcern;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DriverConcernRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        /** @var DriverConcern|null $concern */
        $concern = $this->route('driver_concern');

        $this->merge([
            'status' => $this->input('status', $concern?->status ?? DriverConcern::STATUSES[0]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reported_at' => ['required', 'date'],
            'driver_id' => ['required', 'exists:drivers,id'],
            'vehicle_licence_plate' => ['nullable', 'string', 'max:50'],
            'concern_type_id' => ['required', 'exists:concern_types,id'],
            'description' => ['nullable', 'string'],
            'immediate_action' => ['nullable', 'string'],
            'responsible_party' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(DriverConcern::STATUSES)],
            'resolution_comments' => ['nullable', 'string'],
            'completion_date' => ['nullable', 'date', 'after_or_equal:reported_at'],
        ];
    }
}

