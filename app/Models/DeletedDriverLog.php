<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletedDriverLog extends Model
{
    use HasFactory;
     protected $fillable = [
        'driver_name',
        'driver_id',
        'company_id',
        'deleted_by'
    ];

}
