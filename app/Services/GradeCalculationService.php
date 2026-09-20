<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\GradeFinalScore;
use App\Models\GradeFormatif;
use App\Models\GradeSumatif;
use Illuminate\Support\Facades\DB;

class GradeCalculationService
{
    public static function calculateForStudent(int $enrollmentId, int $subjectId): GradeFinalScore
    {
        return DB::transaction(function () use ($enrollmentId, $subjectId) {
            $formatifQuery = GradeFormatif::where('enrollment_id', $enrollmentId)
                ->whereHas('learningObjective', function ($q) use ($subjectId) {
                    $q->where('subject_id', $subjectId);
                });

            $formativeAvg = $formatifQuery->avg('score') ?? 0;

            $highestFormatif = (clone $formatifQuery)
                ->with('learningObjective')
                ->orderByDesc('score')
                ->first();

            $lowestFormatif = (clone $formatifQuery)
                ->with('learningObjective')
                ->orderBy('score')
                ->first();

            $highestDesc = $highestFormatif && $highestFormatif->learningObjective
                ? 'Menunjukkan penguasaan yang sangat baik dalam '.lcfirst($highestFormatif->learningObjective->description)
                : 'Menunjukkan penguasaan materi yang baik.';

            $lowestDesc = $lowestFormatif && $lowestFormatif->learningObjective && $lowestFormatif->score < 70
                ? 'Perlu bimbingan dan pendampingan lebih lanjut dalam '.lcfirst($lowestFormatif->learningObjective->description)
                : 'Menunjukkan peningkatan yang konsisten dalam pembelajaran.';

            $sumativeSlmAvg = GradeSumatif::where('enrollment_id', $enrollmentId)
                ->where('subject_id', $subjectId)
                ->where('type', 'slm')
                ->avg('score') ?? 0;

            $sumativeSas = GradeSumatif::where('enrollment_id', $enrollmentId)
                ->where('subject_id', $subjectId)
                ->where('type', 'sas')
                ->value('score') ?? 0;

            $finalScore = ($sumativeSlmAvg > 0 || $sumativeSas > 0)
                ? round((($sumativeSlmAvg * 2) + $sumativeSas) / 3, 2)
                : round($formativeAvg, 2);

            return GradeFinalScore::updateOrCreate(
                [
                    'enrollment_id' => $enrollmentId,
                    'subject_id' => $subjectId,
                ],
                [
                    'formative_avg' => round($formativeAvg, 2),
                    'sumative_slm_avg' => round($sumativeSlmAvg, 2),
                    'sumative_sas' => round($sumativeSas, 2),
                    'final_score' => $finalScore,
                    'highest_achieved_description' => $highestDesc,
                    'lowest_achieved_description' => $lowestDesc,
                ]
            );
        });
    }

    public static function calculateBatchForClassroom(int $classId, int $subjectId): void
    {
        // Menggunakan class_id sesuai struktur Model Enrollment
        $enrollments = Enrollment::where('class_id', $classId)->pluck('id');

        foreach ($enrollments as $enrollmentId) {
            self::calculateForStudent($enrollmentId, $subjectId);
        }
    }
}
