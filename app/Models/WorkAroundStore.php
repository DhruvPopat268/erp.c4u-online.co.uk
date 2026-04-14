<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAroundStore extends Model
{
    use HasFactory;
    protected $fillable = [
        'profile_id',
        'operating_centres',
        'vehicle_id',
        'speedo_odometer',
        'fuel_level',
        'adblue_level',
        'step',
        'status',
        'reason',
        'question_id',
        'driver_id',
        'company_id',
        'start_date',
        'end_date',
        'uploaded_date',
        'signature',
        'duration',
        'defects_count',
        'rectified',
        'lat_lng',
        'location'
        
    ];
    
     public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'company_id');
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(\App\Models\Vehicles::class);
    }

    public function depot()
    {
        return $this->belongsTo(\App\Models\Depot::class, 'operating_centres');
    }

    public function workAroundQuestionAnswers()
{
    return $this->hasMany(\App\Models\WorkAroundQuestionAnswerStore::class, 'workaround_store_id'); // Adjust 'work_around_store_id' if needed
}

public function profile()
{
    return $this->belongsTo(\App\Models\WorkAroundProfile::class, 'profile_id');
}
}
