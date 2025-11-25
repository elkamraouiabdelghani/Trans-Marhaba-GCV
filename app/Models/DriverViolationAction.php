<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverViolationAction extends Model
{
    protected $fillable = [
        'driver_violation_id',
        'analysis',
        'action_plan',
        'evidence_path',
        'evidence_original_name',
    ];

    public function driverViolation(): BelongsTo
    {
        return $this->belongsTo(DriverViolation::class);
    }
}
