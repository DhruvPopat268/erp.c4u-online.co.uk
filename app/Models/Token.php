<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'issue_number',
        'valid_from_date',
        'valid_to_date',

    ];

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class);
    }
}
