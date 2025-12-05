<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyBlackPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'journey_id',
        'name',
        'latitude',
        'longitude',
        'description',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Journey this black point belongs to.
     */
    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }
}

