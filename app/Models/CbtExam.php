<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtExam extends Model
{
    use HasFactory;

    protected $table = 'cbt_exams';

    protected $fillable = ['subject_id', 'teacher_id', 'title', 'duration_minutes', 'token', 'randomize_questions', 'randomize_options', 'show_result', 'start_time', 'end_time', 'is_active'];

    protected $casts = [
        'randomize_questions' => 'boolean',
        'randomize_options' => 'boolean',
        'show_result' => 'boolean',
        'is_active' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CbtQuestion::class, 'cbt_exam_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CbtExamSession::class, 'cbt_exam_id');
    }
}