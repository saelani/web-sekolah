<?php

namespace App\Imports;

use App\Models\LearningObjective;
use App\Models\SumativeScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LearningObjectivesImport implements ToCollection, WithHeadingRow
{
    protected int $subjectId;
    protected int $sumativeScopeId; // Ditambahkan untuk relasi bab
    protected string $phase;
    protected int $level;
    protected int $semester;

    public function __construct(int $subjectId, int $sumativeScopeId, string $phase, int $level, int $semester)
    {
        $this->subjectId = $subjectId;
        $this->sumativeScopeId = $sumativeScopeId;
        $this->phase = $phase;
        $this->level = $level;
        $this->semester = $semester;
    }

    public function collection(Collection $rows): void
    {
        $user = auth()->user();
        $table = (new LearningObjective)->getTable();

        // Ambil data SumativeScope untuk mendapatkan nama bab jika diperlukan di background
        $scope = SumativeScope::find($this->sumativeScopeId);

        // 1. Query dasar penghitungan urutan TP berdasarkan sumative_scope_id
        $query = LearningObjective::where('sumative_scope_id', $this->sumativeScopeId);

        // 2. Cek kolom secara dinamis sebelum menambahkan filter user/teacher jika ada
        if (Schema::hasColumn($table, 'user_id')) {
            $query->where('user_id', $user->id);
        } elseif (Schema::hasColumn($table, 'teacher_id') && $user->teacher) {
            $query->where('teacher_id', $user->teacher->id);
        }

        $lastCount = $query->count();

        foreach ($rows as $row) {
            $description = $row['deskripsi'] ?? $row['description'] ?? null;

            if (empty($description)) {
                continue;
            }

            $lastCount++;
            $generatedCode = "TP-{$lastCount}"; // Bisa disesuaikan format kodenya

            $insertData = [
                'subject_id'        => $this->subjectId,
                'sumative_scope_id' => $this->sumativeScopeId, // Menyimpan relasi bab
                'phase'             => $this->phase,
                'level'             => $this->level,
                'semester'          => $this->semester,
                'code'              => $generatedCode,
                'description'       => trim($description),
            ];

            // 3. Tangani kolom lama (chapter_number & chapter_name) secara aman jika kolomnya masih ada di database
            $chapterNumber = $row['bab'] ?? $row['chapter_number'] ?? $row['lingkup_materi'] ?? null;
            $chapterName   = $row['nama_bab'] ?? $row['chapter_name'] ?? $row['nama_lingkup_materi'] ?? ($scope ? $scope->name : null);

            if (Schema::hasColumn($table, 'chapter_number')) {
                $insertData['chapter_number'] = $chapterNumber ? trim($chapterNumber) : '-';
            }
            if (Schema::hasColumn($table, 'chapter_name')) {
                $insertData['chapter_name'] = $chapterName ? trim($chapterName) : '-';
            }

            // Masukkan ID Pembuat sesuai ketersediaan kolom di tabel
            if (Schema::hasColumn($table, 'user_id')) {
                $insertData['user_id'] = $user->id;
            }

            if (Schema::hasColumn($table, 'teacher_id') && $user->teacher) {
                $insertData['teacher_id'] = $user->teacher->id;
            }

            LearningObjective::create($insertData);
        }
    }
}