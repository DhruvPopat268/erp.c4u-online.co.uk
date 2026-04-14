<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSender extends Model
{
    use HasFactory;

    protected $fillable = [
        'companyName',
        'files',
        'status',
        'created_by',

    ];

    public function types()
    {
        return $this->hasOne('App\Models\CompanyDetails', 'id', 'companyName');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function getFileCountAttribute()
    {
        $files = json_decode($this->files, true);

        return is_array($files) ? count($files) : 0;
    }

    public function companyDetails()
    {
        return $this->belongsTo(\App\Models\CompanyDetails::class, 'companyName');
    }
}
