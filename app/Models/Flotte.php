<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flotte extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Assuming an existing `flottes` table in the shared database.
     */
    protected $table = 'flottes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Disable timestamps if the existing table doesn't have them.
     * Set to true if `created_at` and `updated_at` exist in your table.
     */
    public $timestamps = false;

    /**
     * Get the vehicles for the flotte.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    /**
     * Get the drivers for the flotte.
     */
    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    /**
     * Get the formations for the flotte.
     */
    public function formations()
    {
        return $this->hasMany(Formation::class);
    }

    /**
     * Get all coaching sessions for the flotte.
     */
    public function coachingSessions()
    {
        return $this->hasMany(CoachingSession::class);
    }
}
