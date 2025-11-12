<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Turnover extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'departure_date',
        'flotte',
        'driver_id',
        'user_id',
        'position',
        'departure_reason',
        'interview_notes',
        'interviewed_by',
        'observations',
        'turnover_pdf_path',
        'status',
        'confirmed_at',
        'confirmed_by',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'departure_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Get the driver associated with this turnover.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the user (administration staff) associated with this turnover.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who confirmed this turnover.
     */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Confirm the turnover and update driver/user status if applicable.
     */
    public function confirm(): bool
    {
        DB::beginTransaction();
        try {
            $confirmedAt = now();
            
            // Update turnover status first
            $this->update([
                'status' => 'confirmed',
                'confirmed_at' => $confirmedAt,
                'confirmed_by' => Auth::id(),
            ]);

            // If this turnover is for a driver (position = 'Chauffeur')
            if ($this->position === 'Chauffeur' && $this->driver_id) {
                $driver = $this->driver;
                if ($driver) {
                    $driver->update([
                        'is_integrated' => 0,
                        'status' => 'terminated',
                        'terminated_date' => $confirmedAt->toDateString(),
                        'assigned_vehicle_id' => null,
                        'flotte_id' => null,
                    ]);
                }
            }
            // If this turnover is for an administration staff (user)
            elseif ($this->user_id) {
                $user = $this->user;
                if ($user) {
                    $user->update([
                        'department' => 'other',
                        'status' => 'inactive',
                        'role' => 'other',
                    ]);
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check if turnover is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if turnover is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the name of the person (driver or user).
     */
    public function getPersonNameAttribute(): ?string
    {
        if ($this->driver_id && $this->driver) {
            return $this->driver->full_name;
        }
        
        if ($this->user_id && $this->user) {
            return $this->user->name;
        }

        return null;
    }
}
