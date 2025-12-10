<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoachingChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'coaching_checklist_category_id',
        'label',
        'score',
        'sort_order',
        'is_active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(CoachingChecklistCategory::class, 'coaching_checklist_category_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CoachingSessionChecklistItemAnswer::class, 'coaching_checklist_item_id');
    }
}

