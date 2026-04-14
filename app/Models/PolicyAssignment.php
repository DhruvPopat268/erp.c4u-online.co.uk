<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'policy_type',
        'policy_id',
         'company_id',
         'status',
         'assigned_by',
         'version',
         'policy_version',
         'description',
         'reviewed_on',
         'next_review_date',
         'start_time',
         'end_time',
         'duration',
         'comment'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\CompanyDetails::class, 'company_id');
    }
    public function policyAssignments()
    {
        return $this->hasMany(\App\Models\PolicyAssignment::class, 'policy_id');
    }
    
    public function companyDetails()
{
    return $this->belongsTo(CompanyDetails::class, 'company_id');
}


    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_by');
    }

    // public function versions()
    // {
    //     return $this->hasMany(\App\Models\PolicyAssignmentVersion::class);
    // }

    public function policy()
    {
        // Define relationship based on policy type
        switch ($this->policy_type) {
            case 'bronze':
                return $this->belongsTo(ForsBronze::class, 'policy_id');
            case 'silver':
                return $this->belongsTo(ForsSilver::class, 'policy_id');
            case 'gold':
                return $this->belongsTo(ForsGold::class, 'policy_id');
            default:
                return null;
        }
    }
}
