<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dqc extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'issue_date',

    ];

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class);
    }
}
