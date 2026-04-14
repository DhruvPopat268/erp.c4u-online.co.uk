<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForsGold extends Model
{
    use HasFactory;

    protected $fillable = [
        'gold_policy_name',
        'gold_policy_description',

    ];

    public function drivers()
    {
        return $this->belongsToMany(\App\Models\Driver::class, 'driver_gold_policy')->withTimestamps();
    }
}
