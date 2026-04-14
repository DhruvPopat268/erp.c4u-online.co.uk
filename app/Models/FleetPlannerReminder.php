<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetPlannerReminder extends Model
{
    use HasFactory;
    protected $fillable = [
        'fleet_planner_id',
        'next_reminder_date',
        'status',
        'updated_by',
        'comment',
        'parts_cost',
        'labour_cost',
        'total_cost',
        'tyre_cost',
        'type_of_service',
        'service_test_value',
        'secondary_1_test_value',
        'secondary_2_test_value',
        'parking_test_value',
        'confirmation_comment',
        'odometer_reading',
        'vehicle_status',
        'reminder_status',
        'tyre_depth_comment'
    ];

    public function fleet()
    {
        return $this->belongsTo(\App\Models\Fleet::class,'fleet_planner_id');
    }
    
    public function tyreDepth()
    {
        return $this->hasOne(\App\Models\FleetTyreDepth::class);
    }

    // Define the relationship with FleetFileUpload
    public function fileUploads()
    {
        return $this->hasMany(\App\Models\FleetFileUpload::class);
    }
}
