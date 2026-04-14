<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverAttachments extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'license_front',
        'license_back',
        'cpc_card_front',
        'cpc_card_back',
        'tacho_card_front',
        'tacho_card_back',
        'mpqc_card_front',
        'mpqc_card_back',
        'levelD_card_front',
        'levelD_card_back',
        'one_card_front',
        'one_card_back',
        'additional_cards',
        'multipleimagepath',
        'created_by',

    ];

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class);
    }

    protected $casts = [
        'additional_cards' => 'array',
    ];
}
