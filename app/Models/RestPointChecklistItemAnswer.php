<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestPointChecklistItemAnswer extends Model
{
    use HasFactory;

    protected $table = 'rest_points_checklist_item_answers';

    protected $fillable = [
        'rest_points_checklist_id',
        'rest_points_checklist_item_id',
        'is_checked',
        'comment',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
    ];

    /**
     * The checklist instance this answer belongs to.
     */
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(RestPointChecklist::class, 'rest_points_checklist_id');
    }

    /**
     * The checklist item (sous-critère) this answer refers to.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(RestPointChecklistItem::class, 'rest_points_checklist_item_id');
    }
}


