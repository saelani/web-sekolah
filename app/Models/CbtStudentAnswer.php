<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtStudentAnswer extends Model
{
    use HasFactory;

    protected $table = 'cbt_student_answers';

    protected $fillable = ['cbt_exam_session_id', 'cbt_question_id', 'cbt_option_id', 'essay_answer', 'is_correct', 'score_given'];

    protected $casts = [
        'is_correct' => 'boolean',
        'score_given' => 'float',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(CbtExamSession::class, 'cbt_exam_session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CbtQuestion::class, 'cbt_question_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(CbtOption::class, 'cbt_option_id');
    }
}
