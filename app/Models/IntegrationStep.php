<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_candidate_id',
        'step_number',
        'step_data',
        'status',
        'validated_by',
        'validated_at',
        'rejected_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'step_data' => 'array',
        'step_number' => 'integer',
        'validated_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the integration candidate that owns this step
     */
    public function integrationCandidate(): BelongsTo
    {
        return $this->belongsTo(IntegrationCandidate::class);
    }

    /**
     * Get the user who validated this step
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Get step data value
     */
    public function getStepData(string $key, $default = null)
    {
        $data = $this->step_data ?? [];
        return $data[$key] ?? $default;
    }

    /**
     * Set step data value
     */
    public function setStepData(string $key, $value): void
    {
        $data = $this->step_data ?? [];
        $data[$key] = $value;
        $this->step_data = $data;
    }

    /**
     * Set multiple step data values
     */
    public function setStepDataArray(array $data): void
    {
        $currentData = $this->step_data ?? [];
        $this->step_data = array_merge($currentData, $data);
    }

    /**
     * Validate this step
     */
    public function validateStep(?int $userId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'validated',
            'validated_by' => $userId,
            'validated_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Reject this step
     */
    public function rejectStep(?string $reason = null, ?int $userId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'validated_by' => $userId,
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Check if step is validated
     */
    public function isValidated(): bool
    {
        return $this->status === 'validated';
    }

    /**
     * Check if step is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if step is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Scope to get only validated steps
     */
    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    /**
     * Scope to get only rejected steps
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to get only pending steps
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get steps for a specific step number
     */
    public function scopeForStep($query, int $stepNumber)
    {
        return $query->where('step_number', $stepNumber);
    }
}
