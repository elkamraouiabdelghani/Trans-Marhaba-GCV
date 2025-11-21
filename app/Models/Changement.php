<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Models\User;
use App\Models\Driver;

class Changement extends Model
{
    use HasFactory;

    protected $fillable = [
        'changement_type_id',
        'subject_type',
        'subject_id',
        'date_changement',
        'description_changement',
        'responsable_changement',
        'impact',
        'action',
        'status',
        'check_list_path',
        'current_step',
        'created_by',
        'validated_by',
        'validated_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date_changement' => 'date',
        'validated_at' => 'datetime',
        'rejected_at' => 'datetime',
        'current_step' => 'integer',
    ];

    /**
     * Get the changement type
     */
    public function changementType(): BelongsTo
    {
        return $this->belongsTo(ChangementType::class);
    }

    /**
     * Get the subject (Driver or User) that this changement is for
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all steps for this changement
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ChangementStep::class);
    }

    /**
     * Get all checklist results for this changement
     */
    public function checklistResults(): HasMany
    {
        return $this->hasMany(ChangementChecklistResult::class);
    }

    /**
     * Get the user who created this changement (if created_by is a user ID)
     */
    public function creator()
    {
        if ($this->created_by && is_numeric($this->created_by)) {
            return $this->belongsTo(User::class, 'created_by');
        }
        return null;
    }

    /**
     * Get the user who validated this changement (if validated_by is a user ID)
     */
    public function validator()
    {
        if ($this->validated_by && is_numeric($this->validated_by)) {
            return $this->belongsTo(User::class, 'validated_by');
        }
        return null;
    }

    /**
     * Get a specific step by step number
     */
    public function getStep(int $stepNumber): ?ChangementStep
    {
        return $this->steps()->where('step_number', $stepNumber)->first();
    }

    /**
     * Check if changement can proceed to a specific step
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
     * Check if changement is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if changement is validated
     */
    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    /**
     * Check if changement is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if changement is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if changement is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Mark changement as rejected
     */
    public function markAsRejected(?string $reason = null, ?string $userId = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'validated_by' => $userId,
        ]);
    }

    /**
     * Mark changement as validated
     */
    public function markAsValidated(?string $userId = null): void
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
        if ($this->current_step >= 6) {
            return;
        }

        $this->update([
            'current_step' => min($this->current_step + 1, 6),
            'status' => 'in_progress',
        ]);
    }

    /**
     * Get the subject (Driver or User) instance
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Check if changement is for a driver
     */
    public function isForDriver(): bool
    {
        return $this->subject_type === Driver::class;
    }

    /**
     * Check if changement is for an administrative user
     */
    public function isForAdministrative(): bool
    {
        return $this->subject_type === User::class;
    }

    /**
     * Get the name of the subject
     */
    public function getSubjectName(): ?string
    {
        $subject = $this->getSubject();
        
        if (!$subject) {
            return null;
        }

        if ($this->isForDriver()) {
            return $subject->full_name ?? null;
        }

        if ($this->isForAdministrative()) {
            return $subject->name ?? null;
        }

        return null;
    }
}
