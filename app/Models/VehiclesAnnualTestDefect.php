<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehiclesAnnualTestDefect extends Model
{
    use HasFactory;

    protected $fillable = [
        'companyName',
        'vehicle_id',
        'annual_test_id',
        'failure_item_no',
        'failure_reason',
        'severity_code',
        'severity_description',
        
        'dangerous',
        'text',
        'type'
    ];

    public function annualTest()
    {
        return $this->belongsTo(\App\Models\VehiclesAnnualTest::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(\App\Models\Vehicles::class);
    }

    public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'companyName');
    }
}
