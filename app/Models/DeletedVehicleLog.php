<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletedVehicleLog extends Model
{
    use HasFactory;

     protected $fillable = [
        'vehicle_registrationnumber',
        'vehicle_id',
        'company_id',
        'deleted_by'
    ];
}
