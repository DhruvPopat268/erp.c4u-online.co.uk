<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TachoCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'card_number',
        'card_status',
        'card_expiry_date',
        'card_start_of_validity_date',

    ];

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class);
    }
}
