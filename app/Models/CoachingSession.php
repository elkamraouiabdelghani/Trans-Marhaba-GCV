<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachingSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'driver_id',
        'flotte_id',
        'date',
        'date_fin',
        'type',
        'route_taken',
        'moniteur',
        'assessment',
        'status',
        'validity_days',
        'next_planning_session',
        'score',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date' => 'date',
        'date_fin' => 'date',
        'next_planning_session' => 'date',
    ];

    /**
     * Get the driver that owns the coaching session.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the flotte that owns the coaching session.
     */
    public function flotte(): BelongsTo
    {
        return $this->belongsTo(Flotte::class);
    }

    /**
     * Check if the session is planned.
     */
    public function isPlanned(): bool
    {
        return $this->status === 'planned';
    }

    /**
     * Check if the session is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Calculate duration in days (date_fin - date).
     */
    public function getDurationInDays(): int
    {
        if (!$this->date || !$this->date_fin) {
            return 0;
        }
        return $this->date->diffInDays($this->date_fin);
    }
}
