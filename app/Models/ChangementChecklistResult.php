<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangementChecklistResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'changement_id',
        'sous_cretaire_id',
        'status',
        'observation',
    ];

    /**
     * Get the changement that owns this checklist result
     */
    public function changement(): BelongsTo
    {
        return $this->belongsTo(Changement::class);
    }

    /**
     * Get the sous cretaire for this checklist result
     */
    public function sousCretaire(): BelongsTo
    {
        return $this->belongsTo(SousCretaire::class);
    }

    /**
     * Check if result is OK
     */
    public function isOk(): bool
    {
        return $this->status === 'OK';
    }

    /**
     * Check if result is KO
     */
    public function isKo(): bool
    {
        return $this->status === 'KO';
    }

    /**
     * Check if result is N/A
     */
    public function isNa(): bool
    {
        return $this->status === 'N/A';
    }
}
