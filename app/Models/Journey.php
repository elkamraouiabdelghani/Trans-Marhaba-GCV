<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Journey extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'from_latitude',
        'from_longitude',
        'from_location_name',
        'to_latitude',
        'to_longitude',
        'to_location_name',
        'details',
    ];

    protected $casts = [
        'from_latitude' => 'decimal:7',
        'from_longitude' => 'decimal:7',
        'to_latitude' => 'decimal:7',
        'to_longitude' => 'decimal:7',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'total_score',
        'last_inspection_date',
        'next_inspection_due_at',
        'inspection_status',
        'status',
    ];

    /**
     * Black points for this journey.
     */
    public function blackPoints(): HasMany
    {
        return $this->hasMany(JourneyBlackPoint::class);
    }

    /**
     * Checklists completed for this journey.
     */
    public function checklists(): HasMany
    {
        return $this->hasMany(JourneyChecklistInstance::class);
    }

    /**
     * Latest checklist based on completion date.
     */
    public function latestChecklist(): HasOne
    {
        return $this->hasOne(JourneyChecklistInstance::class)->latestOfMany('completed_at');
    }

    /**
     * Accessor: total score (sum of all checklist item notes from latest checklist).
     */
    public function getTotalScoreAttribute(): float
    {
        $checklist = $this->relationLoaded('latestChecklist')
            ? $this->latestChecklist
            : $this->checklists()->orderByDesc('completed_at')->first();

        if (!$checklist) {
            return 0.0;
        }

        return $checklist->total_score ?? 0.0;
    }

    /**
     * Accessor: last inspection date (from latest checklist completed_at).
     */
    public function getLastInspectionDateAttribute(): ?Carbon
    {
        $checklist = $this->relationLoaded('latestChecklist')
            ? $this->latestChecklist
            : $this->checklists()->orderByDesc('completed_at')->first();

        return $checklist?->completed_at?->copy()->startOfDay();
    }

    /**
     * Accessor: next inspection due date (6 months after last inspection).
     */
    public function getNextInspectionDueAtAttribute(): ?Carbon
    {
        $last = $this->last_inspection_date;

        return $last ? $last->copy()->addMonthsNoOverflow(6) : null;
    }

    /**
     * Accessor: inspection status: ok, overdue, or no_inspection.
     */
    public function getInspectionStatusAttribute(): string
    {
        $last = $this->last_inspection_date;

        if (!$last) {
            return 'no_inspection';
        }

        $due = $this->next_inspection_due_at;

        if (!$due) {
            return 'no_inspection';
        }

        return Carbon::now()->greaterThanOrEqualTo($due)
            ? 'overdue'
            : 'ok';
    }

    /**
     * Accessor: journey status based on total score.
     * Conditions:
     * - 350 < total_score < 580: excellent
     * - 150 < total_score <= 349: good
     * - 100 < total_score <= 149: average
     * - total_score <= 100: less
     */
    public function getStatusAttribute(): string
    {
        $score = $this->total_score;

        if ($score > 350 && $score < 580) {
            return 'excellent';
        } elseif ($score > 150 && $score <= 349) {
            return 'good';
        } elseif ($score > 100 && $score <= 149) {
            return 'average';
        } else {
            return 'less';
        }
    }
}

