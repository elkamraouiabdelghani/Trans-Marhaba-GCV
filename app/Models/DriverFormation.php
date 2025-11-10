<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverFormation extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'formation_type_id',
        'formation_process_id',
        'status',
        'planned_at',
        'done_at',
        'certificate_path',
        'notes',
    ];

    protected $casts = [
        'planned_at' => 'date',
        'done_at' => 'date',
    ];

    /**
     * Get the driver that owns this formation
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the formation type
     */
    public function formationType(): BelongsTo
    {
        return $this->belongsTo(FormationType::class);
    }

    /**
     * Get the formation process
     */
    public function formationProcess(): BelongsTo
    {
        return $this->belongsTo(FormationProcess::class);
    }

    /**
     * Check if formation is done
     */
    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    /**
     * Check if formation is planned
     */
    public function isPlanned(): bool
    {
        return $this->status === 'planned';
    }

    /**
     * Mark formation as done
     */
    public function markDone(?string $certificatePath = null, ?string $notes = null): void
    {
        $this->update([
            'status' => 'done',
            'done_at' => now(),
            'certificate_path' => $certificatePath ?? $this->certificate_path,
            'notes' => $notes ?? $this->notes,
        ]);
    }

    /**
     * Scope to get only done formations
     */
    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    /**
     * Scope to get only planned formations
     */
    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }
}