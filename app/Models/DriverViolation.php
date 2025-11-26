<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class DriverViolation extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'violation_type_id',
        'violation_date',
        'violation_time',
        'speed',
        'speed_limit',
        'violation_duration_seconds',
        'violation_distance_km',
        'location',
        'location_lat',
        'location_lng',
        'status',
        'vehicle_id',
        'description',
        'analysis',
        'action_plan',
        'evidence_path',
        'evidence_original_name',
        'document_path',
        'created_by',
    ];

    protected $casts = [
        'violation_date' => 'date',
        'violation_time' => 'datetime:H:i',
        'speed' => 'decimal:2',
        'speed_limit' => 'decimal:2',
        'violation_duration_seconds' => 'integer',
        'violation_distance_km' => 'decimal:2',
        'location_lat' => 'decimal:7',
        'location_lng' => 'decimal:7',
        'analysis' => 'string',
        'action_plan' => 'string',
        'evidence_path' => 'string',
        'evidence_original_name' => 'string',
    ];

    /**
     * Get the driver associated with this violation
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the violation type
     */
    public function violationType(): BelongsTo
    {
        return $this->belongsTo(ViolationType::class);
    }

    /**
     * Get the vehicle associated with this violation
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the user who created this violation
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the violation type name
     */
    public function getTypeNameAttribute(): string
    {
        if ($this->violationType) {
            return $this->violationType->name;
        }
        
        return __('messages.not_specified');
    }

    /**
     * Check if violation is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if violation is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if violation is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Mark violation as confirmed
     */
    public function markAsConfirmed(): bool
    {
        return $this->update([
            'status' => 'confirmed',
        ]);
    }

    /**
     * Mark violation as rejected
     */
    public function markAsRejected(): bool
    {
        return $this->update([
            'status' => 'rejected',
        ]);
    }
}
