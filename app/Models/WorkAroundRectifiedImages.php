<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkAroundRectifiedImages extends Model
{
    use HasFactory;

    protected $fillable = [
        'answer_id',
        'image_path'
    ];

    public function answer()
    {
        return $this->belongsTo(WorkAroundQuestionAnswerStore::class, 'answer_id');
    }
}
