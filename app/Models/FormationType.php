<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'obligatoire',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'obligatoire' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get all driver formations for this formation type
     */
    public function driverFormations()
    {
        return $this->hasMany(DriverFormation::class);
    }
}
