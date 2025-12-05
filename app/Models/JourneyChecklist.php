<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JourneyChecklist extends Model
{
    use HasFactory;

    protected $table = 'journeys_checklist';

    protected $fillable = [
        'donnees',
        'cirees_appreciation',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}


