<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverIntegrationStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_integration_id',
        'step_key',
        'status',
        'notes',
        'validated_by',
        'validated_at',
        'payload',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
        'payload' => 'array',
    ];

    /**
     * Get the driver integration that owns this step
     */
    public function driverIntegration(): BelongsTo
    {
        return $this->belongsTo(DriverIntegration::class);
    }

    /**
     * Get the user who validated this step
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Check if step is passed
     */
    public function isPassed(): bool
    {
        return $this->status === 'passed';
    }

    /**
     * Check if step is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if step is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark step as passed
     */
    public function markPassed(?int $userId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'passed',
            'validated_by' => $userId ?? auth()->id(),
            'validated_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Mark step as failed
     */
    public function markFailed(?int $userId = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'failed',
            'validated_by' => $userId ?? auth()->id(),
            'validated_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }
}