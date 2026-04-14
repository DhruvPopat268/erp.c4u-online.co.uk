<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleReminderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'company_id',
        'registration_number',
        'status',
        'reminder_type',
        'reminder_date',
        'reminder_parameter'
    ];
    
     public function vehicle()
    {
        return $this->belongsTo(\App\Models\vehicleDetails::class);
    }
}
