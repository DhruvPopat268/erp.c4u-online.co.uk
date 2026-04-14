<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAroundContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'designation',
        'mobile_no',
        'company_id',
        'address',
        'created_by'

    ];

    public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'company_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
