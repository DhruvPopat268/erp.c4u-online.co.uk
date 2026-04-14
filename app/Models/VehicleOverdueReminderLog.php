<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleOverdueReminderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'company_id',
        'registration_number',
        'overdue_date',
        'reminder_type',
        'status',
        'reminder_parameter'
    ];

}
