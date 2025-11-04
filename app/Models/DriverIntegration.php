<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'current_step',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Step keys constants matching the process flow
    const STEP_IDENTIFICATION_BESOIN = 'identification_besoin';
    const STEP_DESCRIPTION_POSTE = 'description_poste';
    const STEP_PROSPECTION = 'prospection';
    const STEP_SELECTION_DOSSIER = 'selection_dossier';
    const STEP_VERIFICATION_DOCUMENTAIRE = 'verification_documentaire';
    const STEP_SELECTION_ENTRETIEN = 'selection_entretien';
    const STEP_TEST_ORAL_ECRIT = 'test_oral_ecrit';
    const STEP_TEST_CONDUITE = 'test_conduite';
    const STEP_VALIDATION = 'validation';
    const STEP_INDUCTION = 'induction';
    const STEP_SIGNATURE_CONTRAT = 'signature_contrat';
    const STEP_ACCOMPAGNEMENT = 'accompagnement';
    const STEP_VALIDATION_FINALE = 'validation_finale';

    /**
     * Get all step keys in order
     */
    public static function getStepsOrder(): array
    {
        return [
            self::STEP_IDENTIFICATION_BESOIN,
            self::STEP_DESCRIPTION_POSTE,
            self::STEP_PROSPECTION,
            self::STEP_SELECTION_DOSSIER,
            self::STEP_VERIFICATION_DOCUMENTAIRE,
            self::STEP_SELECTION_ENTRETIEN,
            self::STEP_TEST_ORAL_ECRIT,
            self::STEP_TEST_CONDUITE,
            self::STEP_VALIDATION,
            self::STEP_INDUCTION,
            self::STEP_SIGNATURE_CONTRAT,
            self::STEP_ACCOMPAGNEMENT,
            self::STEP_VALIDATION_FINALE,
        ];
    }

    /**
     * Get the driver that owns this integration
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get all steps for this integration
     */
    public function steps(): HasMany
    {
        return $this->hasMany(DriverIntegrationStep::class);
    }

    /**
     * Get a specific step by key
     */
    public function getStep(string $stepKey): ?DriverIntegrationStep
    {
        return $this->steps()->where('step_key', $stepKey)->first();
    }

    /**
     * Check if integration is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'validated' && !is_null($this->completed_at);
    }

    /**
     * Check if integration is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}