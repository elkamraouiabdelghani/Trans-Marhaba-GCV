<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Driver;

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
        'interview_answers',
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
        'interview_answers' => 'array',
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

            // Handle driver turnover
            if (!empty($this->driver_id)) {
                $driverModel = Driver::query()->whereKey($this->driver_id)->first();
                if ($driverModel) {
                    DB::table($driverModel->getTable())
                        ->where($driverModel->getKeyName(), $this->driver_id)
                        ->lockForUpdate()
                        ->get();

                    $terminatedAt = $this->departure_date
                        ? Carbon::parse($this->departure_date)
                        : $confirmedAt;

                    $terminatedCause = $this->departure_reason
                        ? trim((string) $this->departure_reason)
                        : __('messages.turnover_departure_reason_default', ['reference' => $this->reference ?? $this->id]);

                    $driverModel->assigned_vehicle_id = null;
                    $driverModel->flotte_id = null;
                    $driverModel->is_integrated = false;
                    $driverModel->status = 'terminated';
                    if (property_exists($driverModel, 'statu')) {
                        $driverModel->statu = 'terminated';
                    }
                    if (property_exists($driverModel, 'state')) {
                        $driverModel->state = 'terminated';
                    }
                    $driverModel->terminated_date = $terminatedAt->toDateString();
                    $driverModel->terminated_cause = Str::limit($terminatedCause, 500);

                    $driverModel->save();
                }
            }
            // Handle administration turnover
            elseif ($this->user_id) {
                $user = $this->user;
                if ($user) {
                    $terminatedAt = $this->departure_date
                        ? Carbon::parse($this->departure_date)
                        : $confirmedAt;

                    $terminatedCause = $this->departure_reason
                        ? trim((string) $this->departure_reason)
                        : __('messages.turnover_departure_reason_default', ['reference' => $this->reference ?? $this->id]);

                    $user->update([
                        'department' => 'other',
                        'status' => 'terminated',
                        'terminated_date' => $terminatedAt->toDateString(),
                        'terminated_cause' => Str::limit($terminatedCause, 500),
                        'is_integrated' => 0,
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

    /**
     * Determine if the exit interview has been completed.
     */
    public function getInterviewCompletedAttribute(): bool
    {
        $answers = $this->interview_answers['answers'] ?? null;

        return is_array($answers) && !empty($answers);
    }

    /**
     * Helper alias for checking interview completion state.
     */
    public function hasInterviewAnswers(): bool
    {
        return $this->interview_completed;
    }
}
