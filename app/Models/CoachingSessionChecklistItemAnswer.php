<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachingSessionChecklistItemAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'coaching_session_checklist_id',
        'coaching_checklist_item_id',
        'score',
        'comment',
    ];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(CoachingSessionChecklist::class, 'coaching_session_checklist_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(CoachingChecklistItem::class, 'coaching_checklist_item_id');
    }
}

