<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Models\Changement;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'role',
        'department',
        'status',
        'date_integration',
        'is_integrated',
        'terminated_date',
        'terminated_cause',
        'phone',
        'second_tel_number',
        'person_name',
        'relation',
        'email',
        'password',
        'email_verified_at',
        'date_of_birth',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_integration' => 'date',
            'terminated_date' => 'date',
            'is_integrated' => 'boolean',
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Get all turnovers for this user (administration staff).
     */
    public function turnovers()
    {
        return $this->hasMany(Turnover::class);
    }

    /**
     * Get all turnovers confirmed by this user.
     */
    public function confirmedTurnovers()
    {
        return $this->hasMany(Turnover::class, 'confirmed_by');
    }

    /**
     * Get all changements for this administrative user
     */
    public function changements(): MorphMany
    {
        return $this->morphMany(Changement::class, 'subject');
    }
}
