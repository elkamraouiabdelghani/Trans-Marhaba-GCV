<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveIntegrationStepRequest extends FormRequest
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
        $stepNumber = (int) $this->route('stepNumber');

        $integration = $this->route('integration');
        $isDriverIntegration = $integration && $integration->type === 'driver';

        if (!$isDriverIntegration && in_array($stepNumber, [5, 6, 8], true)) {
            return [];
        }

        $emailRules = ['nullable', 'email', 'max:255'];
        if ($stepNumber === 2 && $integration && $integration->type === 'administration') {
            $existingEmail = $this->getIntegrationStepEmail($integration);
            $inputEmail = strtolower(trim((string) $this->input('email')));

            if ($inputEmail === '' || $existingEmail === null || $existingEmail !== $inputEmail) {
            $emailRules[] = Rule::unique('users', 'email');
            }
        }

        return match ($stepNumber) {
            2 => [
                'full_name' => ['required', 'string', 'max:255'],
                'email' => $emailRules,
                'phone' => ['required', 'string', 'max:20'],
                'cin' => ['required', 'string', 'max:50'],
                'date_of_birth' => ['required', 'date'],
                'address' => ['required', 'string'],
                'license_number' => [$isDriverIntegration ? 'required' : 'nullable', 'string', 'max:50'],
                'license_type' => [$isDriverIntegration ? 'required' : 'nullable', 'in:B,C,D,E'],
                'license_issue_date' => [$isDriverIntegration ? 'required' : 'nullable', 'date'],
                'photo' => ['nullable', 'image', 'max:2048'],
                'documents' => ['nullable', 'array'],
                'documents.*' => ['file', 'max:5120'],
            ],
            3 => [
                'verification_date' => ['required', 'date'],
                'verified_by' => ['required', 'string', 'max:255'],
                'result' => ['required', 'in:passed,failed'],
                'documents_reviewed' => ['required', 'array'],
                'documents_reviewed.*' => ['required', 'string', Rule::in($this->getStep3DocumentKeys())],
                'documents_files' => ['nullable', 'array'],
                'documents_files.*' => ['file', 'max:5120'],
                'notes' => ['nullable', 'string'],
            ],
            4 => [
                'test_date' => ['required', 'date'],
                'evaluator' => ['required', 'string', 'max:255'],
                'oral_note' => ['nullable', 'string'],
                'result' => ['required', 'in:passed,failed'],
                'documents' => ['nullable', 'array'],
                'documents.*' => ['file', 'max:5120'],
                'notes' => ['nullable', 'string'],
            ],
            5 => [
                'test_date' => ['required', 'date'],
                'evaluator' => ['required', 'string', 'max:255'],
                'written_score' => ['nullable', 'integer', 'min:0', 'max:100'],
                'result' => ['required', 'in:passed,failed'],
                'documents' => ['nullable', 'array'],
                'documents.*' => ['file', 'max:5120'],
                'notes' => ['nullable', 'string'],
            ],
            6 => [
                'test_date' => ['required', 'date'],
                'instructor' => ['required', 'string', 'max:255'],
                'score' => ['nullable', 'integer', 'min:0', 'max:100'],
                'result' => ['required', 'in:passed,failed'],
                'documents' => ['nullable', 'array'],
                'documents.*' => ['file', 'max:5120'],
                'notes' => ['nullable', 'string'],
            ],
            7 => [
                'validation_date' => ['required', 'date'],
                'validated_by' => ['required', 'string', 'max:255'],
                'induction_date' => ['nullable', 'date'],
                'induction_conducted_by' => ['nullable', 'string', 'max:255'],
                'contract_signed_date' => ['nullable', 'date'],
                'contract_path' => ['nullable', 'string'],
                'contract' => ['nullable', 'array'],
                'contract.*' => ['file', 'mimes:pdf,doc,docx', 'max:5120'],
                'notes' => ['nullable', 'string'],
            ],
            8 => [
                'accompaniment_start_date' => ['required', 'date'],
                'accompaniment_end_date' => ['nullable', 'date', 'after_or_equal:accompaniment_start_date'],
                'accompanied_by' => ['required', 'string', 'max:255'],
                'result' => ['required', 'in:passed,failed'],
                'documents' => ['nullable', 'array'],
                'documents.*' => ['file', 'max:5120'],
                'notes' => ['nullable', 'string'],
            ],
            9 => [
                'final_validation_date' => ['required', 'date'],
                'validated_by' => ['required', 'string', 'max:255'],
                'documents' => ['nullable', 'array'],
                'documents.*' => ['file', 'max:5120'],
                'notes' => ['nullable', 'string'],
            ],
            default => [],
        };
    }

    /**
     * Document keys allowed for step 3 verification.
     *
     * @return array<int, string>
     */
    private function getStep3DocumentKeys(): array
    {
        return [
            'cin',
            'cv',
            'lettre_motivation',
            'permis_conduire',
            'casier_judiciaire',
            'certificat_medical',
            'certificat_yeux',
            'carte_professionnelle',
            'attestation_travail',
            'attestation_demission',
            'formations',
            'sold_permis',
            'rib',
        ];
    }

    /**
     * Retrieve the email saved in step 2 (if any) for the given integration.
     */
    private function getIntegrationStepEmail($integration): ?string
    {
        $step = $integration->getStep(2);
        if (!$step) {
            return null;
        }

        $stepData = $step->step_data;
        if (is_string($stepData)) {
            $decoded = json_decode($stepData, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $stepData = $decoded;
            } else {
                $stepData = [];
            }
        }

        if (!is_array($stepData)) {
            return null;
        }

        return isset($stepData['email']) ? strtolower(trim((string) $stepData['email'])) : null;
    }
}


