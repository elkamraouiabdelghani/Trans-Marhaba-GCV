<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormationProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'formation_type_id',
        'driver_formation_id',
        'site',
        'flotte_id',
        'theme',
        'status',
        'current_step',
        'validated_by',
        'validated_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
        'rejected_at' => 'datetime',
        'current_step' => 'integer',
    ];

    /**
     * Get the driver for this formation process
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    /**
     * Get the formation type
     */
    public function formationType(): BelongsTo
    {
        return $this->belongsTo(FormationType::class, 'formation_type_id');
    }

    /**
     * Get the driver formation record
     */
    public function driverFormation(): BelongsTo
    {
        return $this->belongsTo(DriverFormation::class, 'driver_formation_id');
    }

    /**
     * Get the flotte
     */
    public function flotte(): BelongsTo
    {
        return $this->belongsTo(Flotte::class, 'flotte_id');
    }

    /**
     * Get all steps for this formation process
     */
    public function steps(): HasMany
    {
        return $this->hasMany(FormationStep::class);
    }

    /**
     * Get the user who validated this formation process
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Get a specific step by step number
     */
    public function getStep(int $stepNumber): ?FormationStep
    {
        return $this->steps()->where('step_number', $stepNumber)->first();
    }

    /**
     * Check if process can proceed to a specific step
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
     * Check if formation process is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if formation process is validated
     */
    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    /**
     * Check if formation process is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if formation process is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Mark formation process as rejected
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
     * Mark formation process as validated
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
        if ($this->current_step < 8) {
            $this->update([
                'current_step' => $this->current_step + 1,
                'status' => 'in_progress',
            ]);
        }
    }
}

