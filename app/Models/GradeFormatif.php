<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeFormatif extends Model
{
    use HasFactory;

    protected $table = 'grade_formatifs';

    protected $fillable = ['enrollment_id', 'learning_objective_id', 'score', 'is_achieved'];

    protected $casts = ['is_achieved' => 'boolean'];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function learningObjective(): BelongsTo
    {
        return $this->belongsTo(LearningObjective::class, 'learning_objective_id');
    }
}
