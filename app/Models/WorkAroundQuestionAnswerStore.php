<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAroundQuestionAnswerStore extends Model
{
    use HasFactory;
    protected $fillable = [
        'profile_details_id',
        'question_id',
        'status',
        'reason',
        'image',
        'workaround_store_id',
        'step',
        'problem_type',
        'problem_solution',
        'third_party',
        'defect_options',
        'rectified_username',
        'rectified_date',
        'rectified_signature',
        'other_reason'
    ];

    public function profileDetails()
    {
        return $this->belongsTo(\App\Models\WorkAroundProfileDetails::class, 'profile_details_id');
    }

    public function question()
    {
        return $this->belongsTo(\App\Models\WorkAroundQuestion::class, 'question_id');
    }
    
    public function workAroundStore()
{
    return $this->belongsTo(\App\Models\WorkAroundStore::class, 'workaround_store_id'); // Adjust 'work_around_store_id' if needed
}

public function defectHistory()
{
    return $this->hasOne(\App\Models\WorkAroundDefectsHistories::class, 'workaround_question_answer_id', 'id');
}
public function fileUploads()
{
    return $this->hasMany(\App\Models\WorkAroundRectifiedImages::class, 'answer_id');
}
}
