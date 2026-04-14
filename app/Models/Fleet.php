<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fleet extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'planner_type',
        'start_date',
        'end_date',
        'every',
        'interval',
        'created_by'
    ];

    public function reminders()
{
    return $this->hasMany(\App\Models\FleetPlannerReminder::class,'fleet_planner_id');
}

public function company()
{
    return $this->hasOne('App\Models\CompanyDetails', 'id', 'company_id');
}

    public function vehicle()
    {
        return $this->belongsTo(vehicleDetails::class,'vehicle_id');
    }

}
