<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'companyName',
        'licence_number',
        'traffic_area',
        'continuation_date',
        'transport_manager_name',
        'status',
        'operating_centre',
        'vehicles',
        'trailers',
        'created_by',
        'created_username',
    ];

    public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'companyName');
    }

    public function creator()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_username');
    }
}
