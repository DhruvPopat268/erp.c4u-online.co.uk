<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pcn extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_registration_number',
        'company_id',
        'violation_date',
        'vehicle_id',
        'driver_name',
        'notice_date',
        'location',
        'issuing_authority',
        'type',
        'action',
        'status',
        'comments',
        'attachment',
        'fine_amount',
        'deduction_amount',
        'created_by',
        'depot_id',
        'notice_number'
    ];

    public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'company_id');
    }

    public function creator()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
        public function depot()
    {
        return $this->hasOne('App\Models\Depot', 'id', 'depot_id');
    }
    
       public function vehicle()
{
    return $this->belongsTo(Vehicles::class, 'vehicle_id');
}

}
