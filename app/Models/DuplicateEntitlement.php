<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuplicateEntitlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'duplicate_driver_id',
        'driver_modal_id',
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
        return $this->belongsTo(\App\Models\DuplicateDriver::class);
    }
}
