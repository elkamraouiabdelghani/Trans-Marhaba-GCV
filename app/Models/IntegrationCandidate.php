<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Driver;

class IntegrationCandidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'identification_besoin',
        'poste_type',
        'driver_id',
        'description_poste',
        'prospection_method',
        'prospection_date',
        'notes_prospection',
        'status',
        'current_step',
        'validated_by',
        'validated_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'prospection_date' => 'date',
        'validated_at' => 'datetime',
        'rejected_at' => 'datetime',
        'current_step' => 'integer',
    ];

    /**
     * Get the driver that owns this integration candidate
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    /**
     * Get all steps for this integration candidate
     */
    public function steps(): HasMany
    {
        return $this->hasMany(IntegrationStep::class);
    }

    /**
     * Get the user who validated this integration
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Get a specific step by step number
     */
    public function getStep(int $stepNumber): ?IntegrationStep
    {
        return $this->steps()->where('step_number', $stepNumber)->first();
    }

    /**
     * Check if candidate can proceed to a specific step
     */
    public function canProceedToStep(int $stepNumber): bool
    {
        // Cannot proceed if rejected or already validated
        if ($this->isRejected() || $this->isValidated()) {
            return false;
        }

        // Can always proceed to step 1
        if ($stepNumber === 1) {
            return true;
        }

        // Check if all previous steps are validated
        for ($i = 1; $i < $stepNumber; $i++) {
            if ($this->type !== 'driver' && in_array($i, [5, 6, 8], true)) {
                continue;
            }
            $step = $this->getStep($i);
            if (!$step || $step->status !== 'validated') {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the current step number
     */
    public function getCurrentStep(): int
    {
        return $this->current_step ?? 1;
    }

    /**
     * Check if integration is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if integration is validated
     */
    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    /**
     * Check if integration is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if integration is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Mark integration as rejected
     */
    public function markAsRejected(?string $reason = null, ?int $userId = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'validated_by' => $userId,
        ]);
    }

    /**
     * Mark integration as validated
     */
    public function markAsValidated(?int $userId = null): void
    {
        $this->update([
            'status' => 'validated',
            'validated_at' => now(),
            'validated_by' => $userId,
        ]);
    }

    /**
     * Move to next step
     */
    public function moveToNextStep(): void
    {
        if ($this->current_step >= 9) {
            return;
        }

        $nextStep = $this->current_step + 1;

        if ($this->type !== 'driver') {
            while (in_array($nextStep, [5, 6, 8], true) && $nextStep < 9) {
                $nextStep++;
            }
        }

        $this->update([
            'current_step' => min($nextStep, 9),
            'status' => 'in_progress',
        ]);
    }
}
