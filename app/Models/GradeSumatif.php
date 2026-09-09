<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeSumatif extends Model
{
    use HasFactory;

    protected $table = 'grade_sumatifs';

    protected $fillable = ['enrollment_id', 'subject_id', 'sumative_scope_id', 'type', 'score'];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function sumativeScope(): BelongsTo
    {
        return $this->belongsTo(SumativeScope::class, 'sumative_scope_id');
    }
}