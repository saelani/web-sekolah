<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'acad_students';

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'name',
        'password',
        'gender',
        'religion',
        'pob',
        'dob',
        'address',
        'parent_name',
        'photo_path',
        'class_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'dob' => 'date',
        'password' => 'hashed',
    ];

    // SELALU SERTAKAN PHOTO_URL SAAT SERIALISASI JSON API
    protected $appends = ['photo_url'];

    // Accessor untuk photo_url
    public function getPhotoUrlAttribute()
    {
        if ($this->photo_path) {
            return 'http://192.168.100.234:8004/storage/'.$this->photo_path;
        }

        return null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function savings(): HasMany
    {
        return $this->hasMany(StudentSaving::class, 'student_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'student_id');
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'student_id');
    }
}
