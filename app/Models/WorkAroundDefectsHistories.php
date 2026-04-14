<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAroundDefectsHistories extends Model
{
    use HasFactory;

    protected $fillable = ['workaround_question_answer_id', 'reason', 'image'];
    
        public function workaroundQuestionAnswerStore()
    {
        return $this->belongsTo(\App\Models\WorkAroundQuestionAnswerStore::class, 'workaround_question_answer_id');
    }

}
