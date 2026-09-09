<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    use HasFactory;

    protected $table = 'ops_student_attendances';

    // protected $fillable = ['class_id', 'student_id', 'date', 'status', 'notes'];
    protected $fillable = [
        'class_id',
        'student_id',
        'user_id', // Pastikan user_id didaftarkan
        'date',
        'status',
        'notes',
    ];
    
    protected $casts = ['date' => 'date'];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}