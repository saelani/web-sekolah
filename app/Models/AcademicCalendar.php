<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicCalendar extends Model
{
    use HasFactory;

    protected $table = 'acad_academic_calendars';

    // PASTIKAN SEMUA FIELD INI TERDAFTAR DI FILLABLE
    protected $fillable = [
        'subject_id',
        'class_room_id',
        'learning_objective_id',
        'start_date',
        'end_date',
        'activity_type',
        'title',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_room_id');
    }

    public function learningObjective(): BelongsTo
    {
        return $this->belongsTo(LearningObjective::class, 'learning_objective_id');
    }
}
