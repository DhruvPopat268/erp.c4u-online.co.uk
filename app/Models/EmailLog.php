<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'subject',
        'body',
        'status',
        'sent_at',
        'attachments',
        'name',
        'header_image',
        'header_image_url',
        'button_url',
        'button_text',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
