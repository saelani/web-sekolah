<?php

namespace App\Exports;

use App\Models\ExtracurricularScore;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GradeExtracurricularsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return ExtracurricularScore::with(['enrollment.student', 'enrollment.class', 'extracurricular'])->get();
    }

    public function headings(): array
    {
        return [
            'ID Enrollment',
            'Nama Siswa',
            'Kelas',
            'ID Ekstrakurikuler',
            'Nama Ekstrakurikuler',
            'Nilai / Predikat',
            'Deskripsi'
        ];
    }

    public function map($score): array
    {
        return [
            $score->enrollment_id,
            $score->enrollment?->student?->name ?? '',
            $score->enrollment?->class?->name ?? '',
            $score->extracurricular_id,
            $score->extracurricular?->name ?? '',
            $score->grade,
            $score->description,
        ];
    }
}