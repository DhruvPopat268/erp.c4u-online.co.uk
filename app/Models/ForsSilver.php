<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForsSilver extends Model
{
    use HasFactory;

    protected $fillable = [
        'silver_policy_name',
        'silver_policy_description',
    ];

    public function drivers()
    {
        return $this->belongsToMany(\App\Models\Driver::class, 'driver_silver_policy')->withTimestamps();
    }
}
