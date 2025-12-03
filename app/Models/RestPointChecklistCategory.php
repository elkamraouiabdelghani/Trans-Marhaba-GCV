<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestPointChecklistCategory extends Model
{
    use HasFactory;

    protected $table = 'rest_points_checklist_categories';

    protected $fillable = [
        'name',
        'is_active',
    ];

    /**
     * Items (sous-critères) belonging to this category.
     */
    public function items(): HasMany
    {
        return $this->hasMany(RestPointChecklistItem::class, 'rest_points_checklist_category_id');
    }
}


