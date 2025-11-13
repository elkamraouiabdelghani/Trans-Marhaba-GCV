<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConcernType extends Model
{
    use HasFactory;

    public const STATUSES = [
        'high',
        'medium',
        'low',
    ];

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function driverConcerns(): HasMany
    {
        return $this->hasMany(DriverConcern::class);
    }
}
