<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SumativeScope extends Model
{
    use HasFactory;

    protected $table = 'grade_sumative_scopes';

    protected $fillable = ['subject_id', 'phase', 'semester', 'name'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
