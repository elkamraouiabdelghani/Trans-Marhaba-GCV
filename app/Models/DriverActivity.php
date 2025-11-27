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
        'flotte',
        'asset_description',
        'driver_name',
        'start_time',
        'end_time',
        'work_time',
        'driving_time',
        'rest_time',
        'rest_daily',
        'raison',
        'start_location',
        'overnight_location',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'activity_date' => 'date',
    ];

    /**
     * Get the driver that owns this activity.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
