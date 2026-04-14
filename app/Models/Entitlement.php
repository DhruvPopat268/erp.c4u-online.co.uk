<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entitlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'category_code',
        'category_legal_literal',
        'category_type',
        'from_date',
        'expiry_date',
        'restrictions',
         'restriction_code',
        'restriction_literal',

    ];

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class);
    }
}
