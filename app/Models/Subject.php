<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $table = 'acad_subjects';

    protected $fillable = ['code', 'name', 'category', 'order_number'];

    public function learningObjectives(): HasMany
    {
        return $this->hasMany(LearningObjective::class, 'subject_id');
    }

    public function sumativeScopes(): HasMany
    {
        return $this->hasMany(SumativeScope::class, 'subject_id');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'acad_subject_teacher', 'subject_id', 'teacher_id');
    }

    /**
     * Relasi ke pembagian pengajar mapel per kelas (TeacherSubjectClass)
     */
    public function teacherSubjectClasses(): HasMany
    {
        return $this->hasMany(TeacherSubjectClass::class, 'subject_id');
    }
}
