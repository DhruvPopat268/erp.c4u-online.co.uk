<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationEmailLog extends Model
{
    use HasFactory;
     protected $fillable = [
        'driver_id','company_id','user_id',
        'email','subject','body','status','type'
    ];
}
