<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForsBronze extends Model
{
    use HasFactory;

    protected $fillable = [
        'bronze_policy_name',
        'bronze_policy_description',
         'created_by',
        'companyName'
    ];

    public function drivers()
    {
        return $this->belongsToMany(\App\Models\Driver::class, 'driver_bronze_policy')->withTimestamps();
    }
    
        public function policyAssignments()
    {
        return $this->hasMany(\App\Models\PolicyAssignment::class, 'policy_id');
    }
    
        public function company()
{
    return $this->belongsTo(CompanyDetails::class, 'companyName'); // Adjust 'company_id' as your foreign key
}
}
