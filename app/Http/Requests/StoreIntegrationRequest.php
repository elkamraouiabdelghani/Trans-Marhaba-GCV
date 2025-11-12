<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIntegrationRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'identification_besoin' => ['required', 'string'],
            'poste_type' => ['required', 'in:chauffeur,administration'],
            'description_poste' => ['required', 'string'],
            'prospection_method' => ['required', 'in:reseaux_social,bouche_a_oreil,bureau_recrutement,autre'],
            'prospection_date' => ['nullable', 'date'],
            'nombre_candidats' => ['nullable', 'integer', 'min:0'],
            'notes_prospection' => ['nullable', 'string'],
        ];
    }
}


