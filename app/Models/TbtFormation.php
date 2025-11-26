<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TbtFormation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'participant',
        'status',
        'description',
        'is_active',
        'year',
        'week_start_date',
        'week_end_date',
        'month',
        'notes',
        'documents',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'year' => 'integer',
        'month' => 'integer',
        'documents' => 'array',
    ];

    /**
     * Scope to filter active formations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by year
     */
    public function scopeForYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope to filter by month
     */
    public function scopeForMonth($query, $month)
    {
        return $query->where('month', $month);
    }
}
