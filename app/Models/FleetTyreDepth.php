<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetTyreDepth extends Model
{
    use HasFactory;

    protected $fillable = [
        'fleet_planner_reminder_id','vehicle_id', 'ns_depth_1', 'ns_depth_2', 'ns_depth_3', 'ns_depth_4', 'ns_depth_5', 'ns_depth_6',
        'os_depth_1', 'os_depth_2', 'os_depth_3', 'os_depth_4', 'os_depth_5', 'os_depth_6',
    ];

    public function reminder()
    {
        return $this->belongsTo(\App\Models\FleetPlannerReminder::class);
    }
}
