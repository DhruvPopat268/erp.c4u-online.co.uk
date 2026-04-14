<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class vehicleDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'companyName',
        'vehicle_id',
        'registrationNumber',
        'taxStatus',
        'taxDueDate',
        'motStatus',
        'make',
        'yearOfManufacture',
        'engineCapacity',
        'co2Emissions',
        'fuelType',
        'markedForExport',
        'colour',
        'typeApproval',
        'revenueWeight',
        'euroStatus',
        'dateOfLastV5CIssued',
        'motExpiryDate',
        'wheelplan',
        'monthOfFirstRegistration',
        'tacho_calibration',
        'dvs_pss_permit_expiry',
        'insurance_type',
        'insurance',
        'PMI_intervals',
        'PMI_due',
        'date_of_inspection',
        'odometer_reading',
        'brake_test_due',
        'created_by',
        'vehicle_status',
        'group_id',
        'vehicle_nick_name',
        'tacho_status',
        'dvs_pss_status',
        'insurance_status',
        'PMI_status',
        'brake_test_status',
        'taxDueDate_status',
        'depot_id',
        'fridge_service',
        'fridge_service_interval',
        'fridge_calibration',
        'fridge_calibration_interval',
        'tail_lift',
        'tail_lift_interval',
        'loler',
        'loler_interval',
        'cron_status'
    ];
    
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
    public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'companyName');
    }
    
        public function depot()
    {
        return $this->hasOne('App\Models\Depot', 'id', 'depot_id');
    }

    public function files()
    {
        return $this->hasMany('App\Models\Contract_attachment', 'contract_id', 'id');
    }
    public function group()
    {
        return $this->belongsTo('\App\Models\VehicleGroup', 'group_id');
    }
    public function vehicleGroup()
{
    return $this->belongsTo(\App\Models\VehicleGroup::class, 'group_id'); // Adjust 'group_id' based on your foreign key field
}
    public function ContractAttechment()
    {
        return $this->belongsTo('App\Models\Contract_attachment', 'id', 'contract_id');
    }

    public function vehicle()
    {
        return $this->belongsTo('\App\Models\Vehicles', 'vehicle_id');
    }
}
