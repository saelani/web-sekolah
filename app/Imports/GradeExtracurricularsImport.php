<?php

namespace App\Imports;

use App\Models\ExtracurricularScore;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GradeExtracurricularsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Sesuaikan dengan kunci header pada file excel/csv Anda (misal: enrollment_id, extracurricular_id, grade, description)
        if (empty($row['enrollment_id']) || empty($row['extracurricular_id'])) {
            return null;
        }

        return ExtracurricularScore::updateOrCreate(
            [
                'enrollment_id'      => $row['enrollment_id'],
                'extracurricular_id' => $row['extracurricular_id'],
            ],
            [
                'grade'       => $row['grade'] ?? '-',
                'description' => $row['description'] ?? null,
            ]
        );
    }
}