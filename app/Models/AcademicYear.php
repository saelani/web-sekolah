<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use HasFactory;

    protected $table = 'acad_academic_years';

    protected $fillable = ['year', 'semester', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'academic_year_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'academic_year_id');
    }

    public function p5Projects(): HasMany
    {
        return $this->hasMany(P5Project::class, 'academic_year_id');
    }
}