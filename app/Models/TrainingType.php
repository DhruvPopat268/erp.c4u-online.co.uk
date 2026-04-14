<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingType extends Model
{
    protected $fillable = [
        'name',
        'created_by',
        'company_id'
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function TrainingCourse()
{
    return $this->hasMany(\App\Models\TrainingCourse::class, 'trainingtype_id'); // Adjust 'driver_id' as per your foreign key
}
public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'company_id');
    }
}
