<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverConsentForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_no',
        'company_id',
        'companyName',
        'company_address',
        'postcode',
        'account_number',
        'reference_number',
        'making_an_enquiry',
        'making_an_enquiry_details',
        'reason_for_processing_information',
        'surname',
        'first_name',
        'middle_name',
        'date_of_birth',
        'current_address_line1',
        'current_address_line2',
        'current_address_line3',
        'current_address_posttown',
        'current_address_postcode',
        'licence_address_line1',
        'licence_address_line2',
        'licence_address_line3',
        'licence_address_posttown',
        'licence_address_postcode',
        'driver_licence_no',
        'cpc_information',
        'tacho_information',
        'submitted_date',
        'signature_image'
    ];

    public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'company_id');
    }
    
        public function driver()
{
    return $this->belongsTo(Driver::class, 'driver_licence_no', 'driver_licence_no');
}

}
