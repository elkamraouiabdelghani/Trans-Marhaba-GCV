<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverActivity extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'driver_id',
        'activity_date',
        'start_time',
        'end_time',
        'driving_hours',
        'rest_hours',
        'route_description',
        'compliance_notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'activity_date' => 'date',
        'driving_hours' => 'integer',
        'rest_hours' => 'integer',
    ];

    /**
     * Get the driver that owns this activity.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
