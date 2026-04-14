<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAroundQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'question_type',
        'select_reasonimage',
        'defect_options'

    ];
    
        protected $casts = [
        'defect_options' => 'array',
    ];
}
