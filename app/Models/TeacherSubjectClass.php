<?php

namespace App\Models;

use App\Traits\HasRoleScope; // Integrated HasRoleScope
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherSubjectClass extends Model
{
    use HasFactory, HasRoleScope;

    protected $table = 'acad_teacher_subject_classes';

    protected $fillable = ['teacher_id', 'subject_id', 'class_id', 'academic_year_id'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
