<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbtExamSession extends Model
{
    use HasFactory;

    protected $table = 'cbt_exam_sessions';

    protected $fillable = ['cbt_exam_id', 'student_id', 'start_time', 'max_end_time', 'submitted_at', 'status', 'total_score'];

    protected $casts = [
        'start_time' => 'datetime',
        'max_end_time' => 'datetime',
        'submitted_at' => 'datetime',
        'total_score' => 'float',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(CbtExam::class, 'cbt_exam_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CbtStudentAnswer::class, 'cbt_exam_session_id');
    }
}
