<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningObjective extends Model
{
    use HasFactory;

    protected $table = 'grade_learning_objectives';

    protected $fillable = [
        'subject_id', 
        'phase', 
        'level', 
        'semester', 
        'code', 
        'description',
        'chapter_number', // Tambahan
        'chapter_name',   // Tambahan
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    // Accessor untuk tampilan rapi pada Dropdown Form
    public function getFullTitleAttribute(): string
    {
        $chapterInfo = $this->chapter_number 
            ? "[{$this->chapter_number}" . ($this->chapter_name ? " - {$this->chapter_name}" : "") . "] " 
            : '';

        return "{$chapterInfo}[{$this->code}] {$this->description}";
    }
}