<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class P5Subelement extends Model
{
    use HasFactory;

    protected $table = 'grade_p5_subelements';

    protected $fillable = ['p5_project_id', 'dimension', 'element', 'subelement_name', 'target_narrative'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    public function p5Scores(): HasMany
    {
        return $this->hasMany(GradeP5Score::class, 'p5_subelement_id');
    }
}