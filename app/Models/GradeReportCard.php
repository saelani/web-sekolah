<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeReportCard extends Model
{
    use HasFactory;

    protected $table = 'grade_report_cards';

    protected $fillable = ['enrollment_id', 'sick', 'permission', 'unexcused', 'extracurricular_notes', 'teacher_notes', 'height_weight', 'health_notes', 'is_promoted', 'is_locked', 'place_date_printed', 'printed_at'];

    protected $casts = [
        'is_promoted' => 'boolean',
        'is_locked' => 'boolean',
        'printed_at' => 'datetime',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }
}