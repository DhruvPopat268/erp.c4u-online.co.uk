<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class DriverUser extends Model
{
    use HasApiTokens;
    use HasRoles;
    use Notifiable;

    protected $fillable = [
        'driver_id',
        'username',
        'password',
        'created_by',
        'last_login_at'

    ];
    
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id'); // Adjust as per your schema
    }
}
