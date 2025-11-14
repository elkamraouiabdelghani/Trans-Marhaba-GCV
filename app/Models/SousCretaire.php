<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SousCretaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'principale_cretaire_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function principaleCretaire(): BelongsTo
    {
        return $this->belongsTo(PrincipaleCretaire::class);
    }
}
