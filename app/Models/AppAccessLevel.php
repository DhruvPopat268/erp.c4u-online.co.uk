<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppAccessLevel extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'manager_access',
        'driver_access',
        'created_by'
    ];

    // Cast manager_access and driver_access to array
    protected $casts = [
        'manager_access' => 'array',
        'driver_access' => 'array',
    ];

    // Relation with Company (Assuming Company model exists)
    public function company()
    {
        return $this->belongsTo(CompanyDetails::class);
    }
    public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}
}
