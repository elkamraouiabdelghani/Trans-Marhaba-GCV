<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DriverHandover;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Assuming an existing `vehicles` table in the shared database.
     */
    protected $table = 'vehicles';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'license_plate',
        'vin_number',
        'brand',
        'model',
        'current_mileage',
        'year',
        'fuel_type',
        'status',
        'flotte_id',
    ];

    /**
     * Disable timestamps if the existing table doesn't have them.
     * Set to true if `created_at` and `updated_at` exist in your table.
     */
    public $timestamps = false;

    /**
     * Get the driver assigned to this vehicle.
     */
    public function driver()
    {
        return $this->hasOne(Driver::class, 'assigned_vehicle_id');
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(DriverHandover::class);
    }

    /**
     * Get the flotte that owns this vehicle.
     */
    public function flotte()
    {
        return $this->belongsTo(Flotte::class);
    }
}
