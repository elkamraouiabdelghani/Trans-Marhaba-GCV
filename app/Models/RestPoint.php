<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class RestPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'latitude',
        'longitude',
        'description',
        'created_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'last_inspection_date',
        'next_inspection_due_at',
        'inspection_status',
    ];

    /**
     * Get the user who created this rest point
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Checklists completed for this rest point.
     */
    public function checklists(): HasMany
    {
        return $this->hasMany(RestPointChecklist::class, 'rest_point_id');
    }

    /**
     * Latest checklist based on completion date.
     */
    public function latestChecklist(): HasOne
    {
        return $this->hasOne(RestPointChecklist::class, 'rest_point_id')->latestOfMany('completed_at');
    }

    /**
     * Accessor: last inspection date (falls back to completed_at).
     */
    public function getLastInspectionDateAttribute(): ?Carbon
    {
        $checklist = $this->relationLoaded('latestChecklist')
            ? $this->latestChecklist
            : $this->checklists()->orderByDesc('completed_at')->first();

        return $checklist?->effective_inspection_date;
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

        if (! $last) {
            return 'no_inspection';
        }

        $due = $this->next_inspection_due_at;

        if (! $due) {
            return 'no_inspection';
        }

        return Carbon::now()->greaterThanOrEqualTo($due)
            ? 'overdue'
            : 'ok';
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get the type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'area' => __('messages.area') ?? 'Area',
            'station' => __('messages.station') ?? 'Station',
            'parking' => __('messages.parking') ?? 'Parking',
            'other' => __('messages.other') ?? 'Other',
            default => $this->type,
        };
    }
}

