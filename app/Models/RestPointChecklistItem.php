<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestPointChecklistItem extends Model
{
    use HasFactory;

    protected $table = 'rest_points_checklist_items';

    protected $fillable = [
        'rest_points_checklist_category_id',
        'label',
        'is_active',
    ];

    /**
     * Category (principe-critère) this item belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(RestPointChecklistCategory::class, 'rest_points_checklist_category_id');
    }

    /**
     * Answers for this item across all rest point checklists.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(RestPointChecklistItemAnswer::class, 'rest_points_checklist_item_id');
    }
}


