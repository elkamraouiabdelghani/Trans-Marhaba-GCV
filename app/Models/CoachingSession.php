<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachingSession extends Model
{
    use HasFactory;

    // Type constants
    public const TYPE_INITIAL = 'initial';
    public const TYPE_SUIVI = 'suivi';
    public const TYPE_CORRECTIF = 'correctif';
    public const TYPE_ROUTE_ANALYSING = 'route_analysing';
    public const TYPE_OBC_SUITE = 'obc_suite';
    public const TYPE_OTHER = 'other';

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
     * Get all available types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_INITIAL,
            self::TYPE_SUIVI,
            self::TYPE_CORRECTIF,
            self::TYPE_ROUTE_ANALYSING,
            self::TYPE_OBC_SUITE,
            self::TYPE_OTHER,
        ];
    }

    /**
     * Get type title.
     */
    public function getTypeTitle(): string
    {
        return self::getTypeTitles()[$this->type] ?? $this->type;
    }

    /**
     * Get type color (Bootstrap color class).
     */
    public function getTypeColor(): string
    {
        return self::getTypeColors()[$this->type] ?? 'secondary';
    }

    /**
     * Get all type titles.
     */
    public static function getTypeTitles(): array
    {
        return [
            self::TYPE_INITIAL => 'Analyse de trajet nouveau client - autres / Remontée client',
            self::TYPE_SUIVI => 'Accompagnement normal',
            self::TYPE_CORRECTIF => 'Accompagnement suite accident de transport',
            self::TYPE_ROUTE_ANALYSING => 'Analyse de trajet à risque / Remontée chauffeur',
            self::TYPE_OBC_SUITE => 'Analyse de trajet à risque / Remontée chauffeur',
            self::TYPE_OTHER => 'Autre',
        ];
    }

    /**
     * Get all type colors.
     */
    public static function getTypeColors(): array
    {
        return [
            self::TYPE_INITIAL => 'info',
            self::TYPE_SUIVI => 'primary',
            self::TYPE_CORRECTIF => 'danger',
            self::TYPE_ROUTE_ANALYSING => 'success',
            self::TYPE_OBC_SUITE => 'warning',
            self::TYPE_OTHER => 'secondary',
        ];
    }

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
