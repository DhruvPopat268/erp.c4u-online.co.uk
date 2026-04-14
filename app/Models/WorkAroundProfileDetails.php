<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAroundProfileDetails extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'vehicle_id', 'work_around_question_id', 'work_around_profile_id','reason',
        'image'];

}
