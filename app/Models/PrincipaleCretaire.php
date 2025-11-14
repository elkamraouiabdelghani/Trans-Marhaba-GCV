<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrincipaleCretaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'changement_type_id',
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function changementType(): BelongsTo
    {
        return $this->belongsTo(ChangementType::class);
    }

    public function sousCretaires(): HasMany
    {
        return $this->hasMany(SousCretaire::class);
    }
}
