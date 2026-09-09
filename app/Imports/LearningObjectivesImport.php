<?php

namespace App\Imports;

use App\Models\LearningObjective;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LearningObjectivesImport implements ToCollection, WithHeadingRow
{
    protected int $subjectId;
    protected string $phase;
    protected int $level;
    protected int $semester;

    public function __construct(int $subjectId, string $phase, int $level, int $semester)
    {
        $this->subjectId = $subjectId;
        $this->phase = $phase;
        $this->level = $level;
        $this->semester = $semester;
    }

    public function collection(Collection $rows): void
    {
        $user = auth()->user();
        $table = (new LearningObjective)->getTable();

        // 1. Query dasar penghitungan urutan TP
        $query = LearningObjective::where('subject_id', $this->subjectId)
            ->where('level', $this->level)
            ->where('semester', $this->semester);

        // 2. Cek kolom secara dinamis sebelum menambahkan filter
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

            // Membaca data Bab dan Nama Bab dari Excel (mendukung header Bahasa Indonesia maupun Inggris)
            $chapterNumber = $row['bab'] ?? $row['chapter_number'] ?? $row['lingkup_materi'] ?? null;
            $chapterName   = $row['nama_bab'] ?? $row['chapter_name'] ?? $row['nama_lingkup_materi'] ?? null;

            $lastCount++;
            $generatedCode = "TP-{$this->level}.{$lastCount}";

            $insertData = [
                'subject_id'     => $this->subjectId,
                'phase'          => $this->phase,
                'level'          => $this->level,
                'semester'       => $this->semester,
                'chapter_number' => $chapterNumber ? trim($chapterNumber) : null,
                'chapter_name'   => $chapterName ? trim($chapterName) : null,
                'code'           => $generatedCode,
                'description'    => trim($description),
            ];

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