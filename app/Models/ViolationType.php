<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ViolationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all violations of this type
     */
    public function violations(): HasMany
    {
        return $this->hasMany(DriverViolation::class);
    }

    /**
     * Scope to get only active violation types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only inactive violation types
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
