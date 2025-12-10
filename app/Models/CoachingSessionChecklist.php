<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachingSessionChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'coaching_session_id',
        'completed_by',
        'completed_at',
        'status',
        'meta',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(CoachingSession::class, 'coaching_session_id');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CoachingSessionChecklistItemAnswer::class, 'coaching_session_checklist_id');
    }

    /**
     * Calculate total score and status
     */
    public function getTotalScore(): int
    {
        // Sum all answer scores
        $totalScore = $this->answers()->sum('score');
        
        // Add EPI control score if checked
        $meta = $this->meta ?? [];
        if (!empty($meta['epi_control']['exists'])) {
            $totalScore += 2;
        }
        
        // Add ADR equipment control score if checked
        if (!empty($meta['adr_equipment_control']['exists'])) {
            $totalScore += 2;
        }
        
        return $totalScore;
    }

    /**
     * Get status based on total score
     */
    public function getScoreStatus(): string
    {
        $score = $this->getTotalScore();
        
        if ($score >= 91 && $score <= 100) {
            return 'TRES BON';
        } elseif ($score >= 81 && $score <= 90) {
            return 'BON';
        } elseif ($score >= 71 && $score <= 80) {
            return 'MOYEN';
        } elseif ($score >= 60 && $score <= 70) {
            return 'MEDIORE';
        }
        
        return 'N/A';
    }

    /**
     * Get status badge color
     */
    public function getScoreStatusColor(): string
    {
        $score = $this->getTotalScore();
        
        if ($score >= 91 && $score <= 100) {
            return 'success';
        } elseif ($score >= 81 && $score <= 90) {
            return 'info';
        } elseif ($score >= 71 && $score <= 80) {
            return 'warning';
        } elseif ($score >= 60 && $score <= 70) {
            return 'danger';
        }
        
        return 'secondary';
    }
}

