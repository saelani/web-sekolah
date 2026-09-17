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
        'sumative_scope_id', 
        'phase', 
        'level', 
        'semester', 
        'code', 
        'description',
        'chapter_number', // Sementara tetap diizinkan diisi otomatis
        'chapter_name',   // Sementara tetap diizinkan diisi otomatis
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            // 1. Ambil data otomatis dari SumativeScope jika ada
            if ($model->sumative_scope_id) {
                $scope = SumativeScope::find($model->sumative_scope_id);
                if ($scope) {
                    $model->subject_id = $scope->subject_id;
                    $model->phase = $scope->phase;
                    $model->semester = $scope->semester;
                    
                    // Otomatis isi chapter_name dari nama SumativeScope (sementara)
                    if (empty($model->chapter_name)) {
                        $model->chapter_name = $scope->name;
                    }
                }
            }

            // 2. Isi default chapter_number jika kosong (sementara)
            if (empty($model->chapter_number)) {
                $model->chapter_number = '-';
            }

            // 3. Generate otomatis kode TP (TP-1, TP-2, dst)
            if (empty($model->code) && $model->sumative_scope_id) {
                $count = self::where('sumative_scope_id', $model->sumative_scope_id)->count();
                $model->code = 'TP-' . ($count + 1);
            }
        });
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function sumativeScope(): BelongsTo
    {
        return $this->belongsTo(SumativeScope::class, 'sumative_scope_id');
    }

    public function getFullTitleAttribute(): string
    {
        $scopeName = $this->sumativeScope ? "[{$this->sumativeScope->name}] " : '';
        return "{$scopeName}[{$this->code}] {$this->description}";
    }
}