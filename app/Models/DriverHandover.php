<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverHandover extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'driver_from_id',
        'driver_from_name',
        'driver_to_id',
        'driver_to_name',
        'vehicle_id',
        'vehicle_km',
        'gasoil',
        'handover_date',
        'back_date',
        'location',
        'cause',
        'status',
        'handover_file_path',
        'documents',
        'document_files',
        'equipment',
        'anomalies_description',
        'anomalies_actions',
    ];

    protected $casts = [
        'handover_date' => 'date',
        'back_date' => 'date',
        'documents' => 'array',
        'document_files' => 'array',
        'equipment' => 'array',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';

    public function driverFrom(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_from_id');
    }

    public function driverTo(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_to_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}

