<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAroundNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'message',
        'key',
        'depot_id'

    ];
    
     public function depot()
    {
        return $this->hasOne('App\Models\Depot', 'id', 'depot_id');
    }
}
