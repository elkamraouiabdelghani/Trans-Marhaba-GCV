<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Formation extends Model
{
    use HasFactory;

    public const TYPE_MONDATORY = 'mondatory';
    public const TYPE_OPTIONNEL = 'optionnel';
    public const TYPE_COMPLIMENTAIRE = 'complimentaire';
    public const TYPE_OTHER = 'other';

    public const TYPE_OPTIONS = [
        self::TYPE_MONDATORY,
        self::TYPE_OPTIONNEL,
        self::TYPE_COMPLIMENTAIRE,
        self::TYPE_OTHER,
    ];

    protected $fillable = [
        'type',
        'flotte_id',
        'theme',
        'participant',
        'duree',
        'realizing_date',
        'status',
        'organisme',
        'description',
        'is_active',
        'delivery_type',
        'reference_value',
        'reference_unit',
        'warning_alert_percent',
        'critical_alert_percent',
    ];

    protected $casts = [
        'type' => 'string',
        'is_active' => 'boolean',
        'reference_value' => 'integer',
        'warning_alert_percent' => 'integer',
        'critical_alert_percent' => 'integer',
        'duree' => 'integer',
        'realizing_date' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the flotte this formation belongs to.
     */
    public function flotte(): BelongsTo
    {
        return $this->belongsTo(Flotte::class, 'flotte_id');
    }

    /**
     * Get all driver formations for this formation.
     */
    public function driverFormations()
    {
        return $this->hasMany(DriverFormation::class);
    }

    /**
     * Returns the localized label for the formation type.
     */
    public function getTypeLabelAttribute(): string
    {
        $type = $this->type ?? self::TYPE_OTHER;
        $key = 'messages.formation_type_' . $type;
        $label = __($key);

        return $label !== $key ? $label : ucfirst($type);
    }

    /**
     * List of available type choices with localized labels.
     *
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        $options = [];
        foreach (self::TYPE_OPTIONS as $value) {
            $options[$value] = __('messages.formation_type_' . $value);
        }

        return $options;
    }

    /**
     * Calculate elapsed percentage based on completion date and reference duration.
     * 
     * @param \Carbon\Carbon|null $completionDate
     * @return float|null
     */
    public function calculateElapsedPercent(?\Carbon\Carbon $completionDate): ?float
    {
        if (!$completionDate) {
            return null;
        }

        $referenceDays = $this->getReferenceDurationInDays();
        if (!$referenceDays || $referenceDays <= 0) {
            return null;
        }

        $elapsedDays = $completionDate->diffInDays(now());
        return min(100, ($elapsedDays / $referenceDays) * 100);
    }

    /**
     * Calculate days remaining before reaching the reference duration.
     * 
     * @param \Carbon\Carbon|null $completionDate
     * @return int|null
     */
    public function calculateDaysRemaining(?\Carbon\Carbon $completionDate): ?int
    {
        if (!$completionDate) {
            return null;
        }

        $referenceDays = $this->getReferenceDurationInDays();
        if (!$referenceDays) {
            return null;
        }

        $targetDate = $completionDate->copy()->addDays($referenceDays);
        return now()->diffInDays($targetDate, false);
    }

    /**
     * Get reference duration in days.
     * 
     * @return int|null
     */
    public function getReferenceDurationInDays(): ?int
    {
        if (!$this->reference_value || !$this->reference_unit) {
            return null;
        }

        return match ($this->reference_unit) {
            'months' => $this->reference_value * 30,
            'years' => $this->reference_value * 365,
            'days' => $this->reference_value,
            default => null,
        };
    }

    /**
     * Calculate alert state for a driver formation based on the latest completion date.
     * 
     * @param \Carbon\Carbon|null $latestCompletionDate The most recent completion date
     * @return string 'none'|'warning'|'critical'
     */
    public function calculateAlertState(?\Carbon\Carbon $latestCompletionDate): string
    {
        if (!$latestCompletionDate) {
            return 'none';
        }

        $percent = $this->calculateElapsedPercent($latestCompletionDate);
        $remainingDays = $this->calculateDaysRemaining($latestCompletionDate);

        $isCritical = false;
        $isWarning = false;

        // Check percentage-based alerts
        if ($percent !== null) {
            if ($this->critical_alert_percent !== null && $percent >= $this->critical_alert_percent) {
                $isCritical = true;
            } elseif ($this->warning_alert_percent !== null && $percent >= $this->warning_alert_percent) {
                $isWarning = true;
            }
        }

        if ($isCritical) {
            return 'critical';
        }

        if ($isWarning) {
            return 'warning';
        }

        return 'none';
    }

    /**
     * Get alert summary for a driver formation based on the latest completion date.
     * 
     * @param \Carbon\Carbon|null $latestCompletionDate The most recent completion date
     * @return array
     */
    public function getAlertSummary(?\Carbon\Carbon $latestCompletionDate): array
    {
        return [
            'state' => $this->calculateAlertState($latestCompletionDate),
            'elapsed_percent' => $this->calculateElapsedPercent($latestCompletionDate),
            'days_remaining' => $this->calculateDaysRemaining($latestCompletionDate),
            'reference_days' => $this->getReferenceDurationInDays(),
            'reference_value' => $this->reference_value,
            'reference_unit' => $this->reference_unit,
        ];
    }
}

