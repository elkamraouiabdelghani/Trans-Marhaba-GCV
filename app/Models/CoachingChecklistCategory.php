<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachingChecklistCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
        'is_active',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CoachingChecklistItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}

