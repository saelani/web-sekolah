<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeP5Score extends Model
{
    use HasFactory;

    protected $table = 'grade_p5_scores';

    protected $fillable = ['enrollment_id', 'p5_subelement_id', 'score'];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function subelement(): BelongsTo
    {
        return $this->belongsTo(P5Subelement::class, 'p5_subelement_id');
    }
}
