<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $table = 'acad_teachers';

    protected $fillable = ['user_id', 'nip', 'nuptk', 'name', 'front_title', 'back_title', 'gender', 'photo_path', 'role_type', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function homeroomClasses(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'teacher_id');
    }

    public function teacherSubjectClasses(): HasMany
    {
        return $this->hasMany(TeacherSubjectClass::class, 'teacher_id');
    }
}
