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
        $rules = [
            'code' => ['nullable', 'string', 'max:255'],
            'driver_from_id' => ['nullable', 'exists:drivers,id'],
            'driver_from_name' => ['nullable', 'string', 'max:255'],
            'driver_to_id' => ['nullable', 'exists:drivers,id'],
            'driver_to_name' => ['nullable', 'string', 'max:255'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'vehicle_km' => ['nullable', 'integer', 'min:0'],
            'gasoil' => ['nullable', 'numeric', 'min:0'],
            'handover_date' => ['nullable', 'date'],
            'back_date' => ['nullable', 'date', 'after_or_equal:handover_date'],
            'location' => ['nullable', 'string', 'max:255'],
            'cause' => ['nullable', 'string', 'max:255'],
            'cause_other' => ['nullable', 'string', 'max:255', 'required_if:cause,other'],
            'status' => ['nullable', 'in:pending,confirmed'],
            'handover_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'documents' => ['nullable', 'array'],
            'documents_images' => ['nullable', 'array'],
            'documents_images.options' => ['nullable', 'array'],
            'documents_images.options.*' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:5120'],
            'documents_files' => ['nullable', 'array'],
            'documents_files.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,gif,doc,docx,xls,xlsx', 'max:10240'],
            'removed_files' => ['nullable', 'array'],
            'removed_files.*' => ['nullable', 'string'],
            'equipment' => ['nullable', 'array'],
            'equipment_images' => ['nullable', 'array'],
            'equipment_images.*' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:5120'],
            'equipment_counts' => ['nullable', 'array'],
            'anomalies_description' => ['nullable', 'string'],
            'anomalies_actions' => ['nullable', 'string'],
        ];

        // Add dynamic validation for documents_images keys (excluding 'options')
        // These are the document row images like 'cartes_grises', 'certificats_visite', etc.
        if ($this->has('documents_images')) {
            $images = $this->input('documents_images', []);
            foreach ($images as $key => $value) {
                if ($key !== 'options') {
                    $rules["documents_images.{$key}"] = ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:5120'];
                }
            }
        }

        return $rules;
    }
}

