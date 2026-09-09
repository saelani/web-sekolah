<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSaving extends Model
{
    use HasFactory;

    protected $table = 'ops_student_savings';

    protected $fillable = ['student_id', 'user_id', 'date', 'type', 'amount', 'description'];

    protected $casts = [
        'date' => 'date',
        'amount' => 'float',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}