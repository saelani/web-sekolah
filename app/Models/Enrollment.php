<?php

namespace App\Models;

use App\Traits\HasRoleScope; // Integrated HasRoleScope
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    use HasFactory, HasRoleScope;

    protected $table = 'acad_enrollments';

    protected $fillable = [
        'academic_year_id',
        'class_id',
        'student_id',
    ];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function formatifGrades(): HasMany
    {
        return $this->hasMany(GradeFormatif::class, 'enrollment_id');
    }

    public function sumatifGrades(): HasMany
    {
        return $this->hasMany(GradeSumatif::class, 'enrollment_id');
    }

    public function finalScores(): HasMany
    {
        return $this->hasMany(GradeFinalScore::class, 'enrollment_id');
    }

    public function reportCard(): HasOne
    {
        return $this->hasOne(GradeReportCard::class, 'enrollment_id');
    }
}
