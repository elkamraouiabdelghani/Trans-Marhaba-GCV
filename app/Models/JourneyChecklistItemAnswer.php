<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JourneyChecklistItemAnswer extends Model
{
    use HasFactory;

    protected $table = 'journey_checklist_item_answers';

    protected $fillable = [
        'journey_checklist_id',
        'journeys_checklist_id',
        'weight',
        'score',
        'note',
        'comment',
    ];

    protected $casts = [
        'weight' => 'integer',
        'score' => 'integer',
        'note' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The checklist instance this answer belongs to.
     */
    public function checklist(): BelongsTo
    {
        return $this->belongsTo(JourneyChecklistInstance::class, 'journey_checklist_id');
    }

    /**
     * The checklist template item this answer refers to.
     */
    public function templateItem(): BelongsTo
    {
        return $this->belongsTo(JourneyChecklist::class, 'journeys_checklist_id');
    }

    /**
     * Mutator: auto-calculate note from weight × score when saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($answer) {
            if ($answer->weight && $answer->score) {
                $answer->note = $answer->weight * $answer->score;
            }
        });
    }
}

