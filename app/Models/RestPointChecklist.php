<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestPointChecklist extends Model
{
    use HasFactory;

    protected $table = 'rest_points_checklists';

    protected $fillable = [
        'rest_point_id',
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

    /**
     * Rest point this checklist belongs to.
     */
    public function restPoint(): BelongsTo
    {
        return $this->belongsTo(RestPoint::class);
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
        return $this->hasMany(RestPointChecklistItemAnswer::class, 'rest_points_checklist_id');
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


