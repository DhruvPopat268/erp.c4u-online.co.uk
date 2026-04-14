<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverAPILog extends Model
{
    use HasFactory;

    protected $fillable = [
        'licence_no',
        'companyName',
        'last_lc_check',
        'created',
        'device_token',
        'driver_id'

    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created');
    }

    public function companyDetails()
    {
        return $this->belongsTo(\App\Models\CompanyDetails::class, 'companyName');
    }
    
        public function drivers()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id'); // Ensure 'driver_id' is the correct foreign key
    }
}
