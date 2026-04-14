<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicles extends Model
{
    use HasFactory;

    protected $fillable = [
        'registrations',
        'vehicle_details_id',
        'companyName',
        'make',
        'model',
        'vehicle_type',
        'registration_date',
        'annual_test_expiry_date',
        'annual_test_status',
        'first_used_date',
        'fuel_type',
        'primary_colour',
        'manufacture_date',
        'engine_size',
        'has_outstanding_recall',
        'cron_status'
    ];

    public function annualTests()
    {
        return $this->hasMany(\App\Models\VehiclesAnnualTest::class, 'vehicle_id');
    }

       public function vehicleannualTest()
    {
        return $this->hasOne(\App\Models\VehiclesAnnualTest::class, 'vehicle_id'); // Adjust the foreign key if needed
    }

    public function vehicleGroup()
{
    return $this->belongsTo(\App\Models\VehicleGroup::class, 'group_id'); // Adjust 'group_id' based on your foreign key field
}

    public function defects()
    {
        return $this->hasMany(\App\Models\VehiclesAnnualTestDefect::class);
    }

    public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'companyName');
    }
    public function details()
    {
        return $this->hasOne(\App\Models\vehicleDetails::class, 'vehicle_id');
    }

    public function vehicleDetails()
{
    return $this->hasMany('App\Models\vehicleDetails', 'vehicle_id');
}

public function vehicleDetail()
    {
        return $this->hasOne(\App\Models\vehicleDetails::class, 'vehicle_id');
    }

}
