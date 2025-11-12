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
        'formation_id',
        'formation_process_id',
        'status',
        'planned_at',
        'due_at',
        'done_at',
        'progress_percent',
        'validation_status',
        'certificate_path',
        'notes',
    ];

    protected $casts = [
        'planned_at' => 'date',
        'due_at' => 'date',
        'done_at' => 'date',
    ];

    /**
     * Get the reference value (in raw units) from the related formation.
     */
    public function getReferenceValue(): ?int
    {
        return $this->formation?->reference_value;
    }

    /**
     * Get the reference unit (months|years) from the related formation.
     */
    public function getReferenceUnit(): ?string
    {
        return $this->formation?->reference_unit;
    }

    /**
     * Convert the reference duration to days.
     */
    public function getReferenceDurationInDays(): ?int
    {
        $value = $this->getReferenceValue();
        $unit = $this->getReferenceUnit();

        if (!$value || !$unit) {
            return null;
        }

        return match ($unit) {
            'months' => $value * 30,
            'years' => $value * 365,
            default => null,
        };
    }

    /**
     * Determine the baseline date used to compute elapsed time.
     */
    public function getCompletionDate(): ?\Carbon\Carbon
    {
        if ($this->done_at) {
            return $this->done_at->copy();
        }

        if ($this->planned_at) {
            return $this->planned_at->copy();
        }

        return null;
    }

    /**
     * Calculate the percentage of the reference duration that has elapsed.
     */
    public function getElapsedPercent(): ?float
    {
        $completionDate = $this->getCompletionDate();
        $referenceDays = $this->getReferenceDurationInDays();

        if (!$completionDate || !$referenceDays || $referenceDays <= 0) {
            return null;
        }

        $elapsedDays = $completionDate->diffInDays(now());

        return min(100, ($elapsedDays / $referenceDays) * 100);
    }

    /**
     * Determine how many days remain before reaching the reference duration.
     */
    public function getDaysRemaining(): ?int
    {
        $completionDate = $this->getCompletionDate();
        $referenceDays = $this->getReferenceDurationInDays();

        if (!$completionDate || !$referenceDays) {
            return null;
        }

        $targetDate = $completionDate->copy()->addDays($referenceDays);

        return now()->diffInDays($targetDate, false);
    }

    /**
     * Determine the current alert state (none|warning|critical).
     * This method uses the Formation model's alert calculation.
     * 
     * @deprecated Use Formation::calculateAlertState() with the latest completion date instead
     */
    public function getAlertState(): string
    {
        $formation = $this->formation;

        if (!$formation) {
            return 'none';
        }

        $completionDate = $this->getCompletionDate();
        return $formation->calculateAlertState($completionDate);
    }

    /**
     * Provide a summary of the alert data for display purposes.
     * This method uses the Formation model's alert calculation.
     * 
     * @deprecated Use Formation::getAlertSummary() with the latest completion date instead
     */
    public function getAlertSummary(): array
    {
        $formation = $this->formation;

        if (!$formation) {
            return [
                'state' => 'none',
                'elapsed_percent' => null,
                'days_remaining' => null,
                'reference_days' => null,
                'reference_value' => null,
                'reference_unit' => null,
            ];
        }

        $completionDate = $this->getCompletionDate();
        return $formation->getAlertSummary($completionDate);
    }

    /**
     * Get the driver that owns this formation
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the formation
     */
    public function formation(): BelongsTo
    {
        return $this->belongsTo(Formation::class);
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

    /**
     * Calculate the next realizing date based on the last done date and formation reference.
     * 
     * @return \Carbon\Carbon|null
     */
    public function getNextRealizingDate(): ?\Carbon\Carbon
    {
        if (!$this->done_at || !$this->formation) {
            return null;
        }

        $referenceValue = $this->formation->reference_value;
        $referenceUnit = $this->formation->reference_unit;

        if (!$referenceValue || !$referenceUnit) {
            return null;
        }

        $nextDate = $this->done_at->copy();

        return match ($referenceUnit) {
            'years' => $nextDate->addYears($referenceValue),
            'months' => $nextDate->addMonths($referenceValue),
            'days' => $nextDate->addDays($referenceValue),
            default => null,
        };
    }

    /**
     * Get the next realizing date formatted as string.
     * 
     * @param string $format
     * @return string|null
     */
    public function getNextRealizingDateFormatted(string $format = 'd/m/Y'): ?string
    {
        $nextDate = $this->getNextRealizingDate();
        return $nextDate ? $nextDate->format($format) : null;
    }
}