<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAroundProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'mobile_app_enabled',
        'company_id',

    ];

    public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'company_id');
    }
    
        public function profileDetails()
    {
        return $this->hasMany(\App\Models\WorkAroundProfileDetails::class, 'work_around_question_id');
    }

    public function questions()
    {
        return $this->hasMany(\App\Models\WorkAroundProfileDetails::class, 'work_around_profile_id');
    }
}
