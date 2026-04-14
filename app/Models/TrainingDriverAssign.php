<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingDriverAssign extends Model
{
    use HasFactory;

    protected $fillable = [
          'training_id',
        'driver_id',
        'from_date',
        'to_date',
        'status',
        'signature',
         'reason',
        'file'
    ];

    public function training()
    {
        return $this->belongsTo(\App\Models\Training::class);
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class); // Assuming you have a Driver model
    }
}
