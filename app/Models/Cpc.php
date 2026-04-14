<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cpc extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'lgv_valid_to',

    ];

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class);
    }
}
