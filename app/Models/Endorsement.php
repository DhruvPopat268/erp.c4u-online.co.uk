<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Endorsement extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'penalty_points',
        'offence_code',
        'offence_legal_literal',
        'offence_date',
        'conviction_date',

    ];

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class);
    }
}
