<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverTbtFormation extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'tbt_formation_id',
        'status',
        'planned_at',
        'done_at',
        'validation_status',
        'notes',
    ];

    protected $casts = [
        'planned_at' => 'date',
        'done_at' => 'date',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function tbtFormation(): BelongsTo
    {
        return $this->belongsTo(TbtFormation::class);
    }
}

