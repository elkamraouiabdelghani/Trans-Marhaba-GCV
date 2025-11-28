<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:255'],
            'driver_from_id' => ['nullable', 'exists:drivers,id'],
            'driver_from_name' => ['nullable', 'string', 'max:255'],
            'driver_to_id' => ['nullable', 'exists:drivers,id'],
            'driver_to_name' => ['nullable', 'string', 'max:255'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'vehicle_km' => ['nullable', 'integer', 'min:0'],
            'gasoil' => ['nullable', 'numeric', 'min:0'],
            'handover_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'cause' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:pending,confirmed'],
            'handover_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'documents' => ['nullable', 'array'],
            'equipment' => ['nullable', 'array'],
            'equipment_counts' => ['nullable', 'array'],
            'anomalies_description' => ['nullable', 'string'],
            'anomalies_actions' => ['nullable', 'string'],
        ];
    }
}

