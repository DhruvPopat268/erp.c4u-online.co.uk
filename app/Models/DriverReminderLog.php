<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverReminderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'company_id',
        'reminder_type',
        'reminder_date',
        'status',
        'reminder_parameter'

    ];
}
