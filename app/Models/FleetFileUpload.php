<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FleetFileUpload extends Model
{
    use HasFactory;
    protected $fillable = ['fleet_planner_reminder_id', 'file_path'];

    public function reminder()
    {
        return $this->belongsTo(\App\Models\FleetPlannerReminder::class);
    }


}
