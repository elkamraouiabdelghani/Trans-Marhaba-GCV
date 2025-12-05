<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JourneyChecklistInstance extends Model
{
    use HasFactory;

    protected $table = 'journey_checklists';

    protected $fillable = [
        'journey_id',
        'completed_by',
        'completed_at',
        'status',
        'notes',
        'documents',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'documents' => 'array',
    ];

    protected $appends = [
        'total_score',
    ];

    /**
     * Journey this checklist belongs to.
     */
    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    /**
     * User who completed the checklist.
     */
    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Answers for each checklist item.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(JourneyChecklistItemAnswer::class, 'journey_checklist_id');
    }

    /**
     * Accessor: total score (sum of all answer notes).
     */
    public function getTotalScoreAttribute(): float
    {
        // If answers are already loaded, use the collection
        if ($this->relationLoaded('answers')) {
            return (float) $this->answers->sum('note');
        }

        // Otherwise query the database
        return (float) ($this->answers()->sum('note') ?? 0.0);
    }

    /**
     * Effective inspection date: we rely on completed_at.
     */
    public function getEffectiveInspectionDateAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->completed_at
            ? $this->completed_at->copy()->startOfDay()
            : null;
    }
}

