<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverConcern extends Model
{
    use HasFactory;

    protected $fillable = [
        'reported_at',
        'driver_id',
        'vehicle_licence_plate',
        'concern_type_id',
        'description',
        'immediate_action',
        'responsible_party',
        'status',
        'resolution_comments',
        'completion_date',
    ];

    protected $casts = [
        'reported_at' => 'date',
        'completion_date' => 'date',
    ];

    public const STATUSES = [
        'open',
        'in_progress',
        'resolved',
        'closed',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function concernType(): BelongsTo
    {
        return $this->belongsTo(ConcernType::class);
    }
}
