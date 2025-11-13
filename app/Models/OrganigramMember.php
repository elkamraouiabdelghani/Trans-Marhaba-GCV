<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganigramMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'position',
        'name',
    ];

    public const POSITIONS = [
        'DG',
        'DGA',
        'COMPTABILITE',
        'IT',
        'OBC',
        'HSSE',
        'DEPOT_ET_EXPLOITATION',
        'MAINTENANCE',
        'MONITEUR',
        'DEPOT',
        'CHAUFFEURS',
    ];
}

