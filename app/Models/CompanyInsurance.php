<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInsurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'insurance_type', 'insurance_date'
    ];

    public function company()
    {
        return $this->belongsTo(CompanyDetails::class, 'company_id');
    }
    

}
