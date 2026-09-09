<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P5Project extends Model
{
    use HasFactory;

    protected $table = 'grade_p5_projects';

    protected $fillable = ['academic_year_id', 'phase', 'theme', 'title', 'description'];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function subelements(): HasMany
    {
        return $this->hasMany(P5Subelement::class, 'p5_project_id');
    }
}