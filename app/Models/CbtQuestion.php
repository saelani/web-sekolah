<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtQuestion extends Model
{
    use HasFactory;

    protected $table = 'cbt_questions';

    protected $fillable = ['cbt_exam_id', 'type', 'question_text', 'media_path', 'score_weight'];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(CbtOption::class, 'cbt_question_id');
    }
}