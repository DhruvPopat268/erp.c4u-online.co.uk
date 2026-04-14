<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDetails extends Model
{
    use HasFactory;

    protected $fillable = [
       'name',
        'email',
        'address',
        'contact',
        'account_no',
        'director_name',
        'director_dob',
        'device',
        'operator_name',
        'operator_phone',
        'status',
        'compliance',
        'operator_email',
        'created_by',
        'created_username',
        'lc_check_status',
        'api_call_count',
        'fors_browse_policy',
        'fors_silver_policy',
        'fors_gold_policy',
        'promotional_email',
        'ptc_library',
                'public_liability',
        'goods_in_transit',
        'public_liability_insurance',
        'payment_type',
        'coins'
    ];
    
   public function creator()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_username');
    }
    public function drivers()
    {
        return $this->hasMany(App\Models\Driver::class, 'companyName');
    }
    
        public function insurances()
{
    return $this->hasMany(CompanyInsurance::class, 'company_id');
}
    

 
}
