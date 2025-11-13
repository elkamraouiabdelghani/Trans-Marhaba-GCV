<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\IntegrationCandidate;

class Driver extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Assuming an existing `drivers` table in the shared database.
     */
    protected $table = 'drivers';

    /**
     * Disable timestamps if the existing table doesn't have them.
     * Set to true if `created_at` and `updated_at` exist in your table.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'address',
        'city',
        'date_of_birth',
        'cin',
        'visite_medical',
        'visite_yeux',
        'formation_imd',
        'formation_16_module',
        'date_integration',
        'attestation_travail',
        'carte_profession',
        'n_cnss',
        'rib',
        'license_number',
        'license_type',
        'license_issue_date',
        'license_class',
        'status',
        'terminated_date',
        'assigned_vehicle_id',
        'notes',
        'documents',
        'flotte_id',
        'is_integrated',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'visite_medical' => 'date',
        'visite_yeux' => 'date',
        'formation_imd' => 'date',
        'formation_16_module' => 'date',
        'date_integration' => 'date',
        'terminated_date' => 'date',
        'license_issue_date' => 'date',
        'documents' => 'array',
    ];

    /**
     * Get the vehicle assigned to this driver.
     */
    public function assignedVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'assigned_vehicle_id');
    }

    /**
     * Get the flotte that owns the driver.
     */
    public function flotte()
    {
        return $this->belongsTo(Flotte::class);
    }

    /**
     * Get the integration candidate that owns this driver
     */
    public function integrationCandidate(): HasOne
    {
        return $this->hasOne(IntegrationCandidate::class, 'driver_id');
    }

    /**
     * Get all formations for this driver
     */
    public function formations()
    {
        return $this->hasMany(DriverFormation::class);
    }

    /**
     * Get all activities for this driver
     */
    public function activities()
    {
        return $this->hasMany(DriverActivity::class);
    }

    /**
     * Get all concerns for this driver
     */
    public function concerns()
    {
        return $this->hasMany(DriverConcern::class);
    }

    /**
     * Get all turnovers for this driver
     */
    public function turnovers()
    {
        return $this->hasMany(Turnover::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->where('status', 'active')
              ->orWhere('status', 'actif')
              ->orWhere('status', 'enabled')
              ->orWhere('status', 'enable')
              ->orWhere('status', '1')
              ->orWhere('status', 'true')
              ->orWhere('status', 'yes')
              ->orWhere('statu', 'active')
              ->orWhere('statu', 'actif')
              ->orWhere('state', 'active')
              ->orWhere('state', 'actif');
        });
    }

    public function scopeInactive($query)
    {
        return $query->where(function($q) {
            $q->where('status', 'inactive')
              ->orWhere('status', 'inactif')
              ->orWhere('status', 'disabled')
              ->orWhere('status', 'disable')
              ->orWhere('status', 'deactive')
              ->orWhere('status', '0')
              ->orWhere('status', 'false')
              ->orWhere('status', 'no')
              ->orWhere('statu', 'inactive')
              ->orWhere('statu', 'inactif')
              ->orWhere('state', 'inactive')
              ->orWhere('state', 'inactif');
        });
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_vehicle_id');
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('assigned_vehicle_id');
    }

    // Helper Methods
    public function isActive(): bool
    {
        $status = strtolower(trim((string)($this->status ?? $this->statu ?? $this->state ?? '')));
        $activeValues = ['active', 'actif', 'enabled', 'enable', '1', 'true', 'yes'];
        return in_array($status, $activeValues, true);
    }

    public function isAssigned(): bool
    {
        return !is_null($this->assigned_vehicle_id);
    }
}