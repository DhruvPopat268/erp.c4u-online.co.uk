<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyPaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'old_payment_type',
        'new_payment_type',
        'old_coins',
        'new_coins',
        'changed_by',
    ];
    
    public function company()
    {
        return $this->belongsTo(CompanyDetails::class, 'company_id');
    }

    /**
     * Relation: Payment history created/updated by a user
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by');
    }
}
