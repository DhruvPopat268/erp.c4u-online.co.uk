<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'training_type_id',
        'training_course_id',
        'from_date',
        'to_date',
        'companyName',
        'driver_id',
        'next_training_date',
        'end_date',
        'status',
        'from_time',
        'to_time',
        'description',
        'created_by',
    ];


    public static $options = [
        'Internal',
        'External',
    ];

    public static $performance = [
        'Not Concluded',
        'Satisfactory',
        'Average',
        'Poor',
        'Excellent',
    ];

    public static $Status = [
        'Pending',
        'Started',
        'Completed',
        'Terminated',
    ];

    public function branches()
    {
        return $this->hasOne('App\Models\Branch', 'id', 'branch');
    }

    public function types()
    {
        return $this->hasOne('App\Models\TrainingType', 'id', 'training_type');
    }

    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee');
    }

    public function trainers()
    {
        return $this->hasOne('App\Models\Trainer', 'id', 'trainer');
    }
   public function trainingCourse()
    {
        return $this->belongsTo(\App\Models\TrainingCourse::class, 'training_course_id'); // Adjust based on your actual foreign key
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function company()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'companyName');
    }

            public function companies()
    {
        return $this->belongsTo(\App\Models\CompanyDetails::class, 'companyName');
    }

    public function trainingType()
    {
        return $this->hasOne('App\Models\TrainingType', 'id', 'training_type_id');
    }

    public function trainingDriverAssigns()
    {
        return $this->hasMany(\App\Models\TrainingDriverAssign::class,'training_id');
    }



}
