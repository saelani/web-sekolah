<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Extracurricular extends Model
{
    use HasFactory;

    protected $table = 'grade_extracurriculars';

    protected $fillable = ['name', 'instructor_name'];

    public function scores(): HasMany
    {
        return $this->hasMany(ExtracurricularScore::class, 'extracurricular_id');
    }
}