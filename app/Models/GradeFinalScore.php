<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeFinalScore extends Model
{
    use HasFactory;

    protected $table = 'grade_final_scores';

    protected $fillable = ['enrollment_id', 'subject_id', 'formative_avg', 'sumative_slm_avg', 'sumative_sas', 'final_score', 'highest_achieved_description', 'lowest_achieved_description'];

    protected $casts = [
        'formative_avg' => 'float',
        'sumative_slm_avg' => 'float',
        'sumative_sas' => 'float',
        'final_score' => 'float',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
