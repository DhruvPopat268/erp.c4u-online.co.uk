<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'companyName',
        'ni_number',
        'contact_no',
        'contact_email',
        'driver_age',
        'driver_dob',
        'driver_address',
        'driver_status',
        'post_code',
        'driver_licence_status',
        'driver_licence_no',
        'last_name',
        'gender',
        'first_names',
        'date_of_birth',
        'address_line1',
        'address_line2',
        'address_line3',
        'address_line4',
        'address_line5',
        'licence_type',
        'tacho_card_no',  // Added field
        'tacho_card_valid_to',  // Added field
        'tacho_card_valid_from',  // Added field
        'token_issue_number',  // Added field
        'token_valid_from_date',  // Added field
        'driver_licence_expiry',  // Added field
        'cpc_validto',  // Added fiel
        'dqc_issue_date',  // Added field
        'endorsement_penalty_points',        // Added fields
        'endorsement_offence_code',
        'endorsement_offence_legal_literal',
        'endorsement_offence_date',
        'endorsement_conviction_date',
        'api_call_count ',
        'endorsements',
        'current_licence_check_interval',
        'next_lc_check',
        'latest_lc_check',
        'created_by',
        'device_token',
        'group_id',
        'automation',
        'consent_valid',
        'depot_id',
        'depot_access_status'
    ];

    public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'companyName');
    }

    public function company()
{
    return $this->belongsTo(CompanyDetails::class, 'companyName'); // Adjust 'company_id' as your foreign key
}

    public function depot()
    {
        return $this->hasOne('App\Models\Depot', 'id', 'depot_id');
    }

    public function setContactNoAttribute($value)
    {
        // Remove any non-digit characters
        $value = preg_replace('/\D/', '', $value);

        // Format the number in UK standard if it's a mobile number
        if (preg_match('/^44/', $value)) {
            $this->attributes['contact_no'] = '+44 '.substr($value, 2);
        } elseif (preg_match('/^07/', $value)) {
            $this->attributes['contact_no'] = '+44 '.substr($value, 1);
        } else {
            $this->attributes['contact_no'] = $value;
        }
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function companyDetails()
    {
        return $this->belongsTo(\App\Models\CompanyDetails::class, 'companyName','id');
    }

    public function attachments()
    {
        return $this->hasMany(\App\Models\DriverAttachments::class, 'driver_id', 'id');
    }

        public function driverUser()
    {
        return $this->hasOne(\App\Models\DriverUser::class, 'driver_id', 'id');
    }

    // public function bronze_policies()
    // {
    //     return $this->belongsToMany(\App\Models\ForsBronze::class);
    // }

    public function bronze_policies()
    {
        return $this->belongsToMany(\App\Models\ForsBronze::class, 'driver_bronze_policy')->withTimestamps();
    }

    public function fors_silvers()
    {
        return $this->belongsToMany(\App\Models\ForsSilver::class, 'driver_silver_policy')->withTimestamps();
    }

    public function fors_gold()
    {
        return $this->belongsToMany(\App\Models\ForsGold::class, 'driver_gold_policy')->withTimestamps();
    }

    // public function getDriverLicenceExpiryAttribute($value)
    // {
    //     if (is_null($value)) {
    //         return null;
    //     }

    //     try {
    //         return \Carbon\Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
    //     } catch (\Exception $e) {
    //         // Log the error or handle it as needed
    //         \Log::error("Date format error: " . $e->getMessage());
    //         return null;
    //     }
    // }

    public function endorsements()
    {
        return $this->hasMany(\App\Models\Endorsement::class);
    }

    public function entitlements()
    {
        return $this->hasMany(\App\Models\Entitlement::class);
    }

    public function tachoCards()
    {
        return $this->hasMany(\App\Models\TachoCard::class);
    }

    public function cpcs()
    {
        return $this->hasMany(\App\Models\Cpc::class);
    }

    public function dqcs()
    {
        return $this->hasMany(\App\Models\Dqc::class);
    }
    public function group()
{
    return $this->belongsTo(\App\Models\Group::class, 'group_id');
}

public function duplicateDrivers()
{
    return $this->hasMany(\App\Models\DuplicateDriver::class, 'driver_modal_id'); // Adjust 'driver_id' as per your foreign key
}

}
