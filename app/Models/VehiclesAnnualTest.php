<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclesAnnualTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'companyName',
        'vehicle_id',
        'test_date',
        'test_type',
        'test_result',
        'test_certificate_number',
        'expiry_date',
        'number_of_defects_test',
        'number_of_advisory_defects_test',
        
        'mot_test_number',
        'completed_date',
        'odometer_value',
        'odometer_unit',
        'odometer_result_type',
        'data_source',
        'location'
    ];

    public function vehicle()
    {
        return $this->belongsTo(\App\Models\Vehicles::class);
    }

    public function defects()
    {
        return $this->hasMany(\App\Models\VehiclesAnnualTestDefect::class, 'annual_test_id');
    }

    public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'companyName');
    }
}
