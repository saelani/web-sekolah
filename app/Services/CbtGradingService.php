<?php

namespace App\Services;

use App\Models\CbtExamSession;
use App\Models\Enrollment;
use App\Models\GradeFinalScore;
use App\Models\GradeFormatif;
use App\Models\GradeSumatif;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CbtGradingService
{
    /**
     * Memproses nilai ujian CBT dan menyinkronkannya ke sistem nilai Kurikulum Merdeka.
     */
    public function calculateAndSyncScore(
        CbtExamSession $session,
        ?int $sumativeScopeId = null,
        string $sumativeType = 'lm'
    ): float {
        return DB::transaction(function () use ($session, $sumativeScopeId, $sumativeType) {
            // Eager loading relasi agar terhindar dari N+1 Query
            $session->loadMissing([
                'exam.questions.options',
                'answers',
                'student',
            ]);

            $totalWeightedScore = 0;
            $maxPossibleScore = 0;

            // 1. Kalkulasi Penilaian Otomatis dari Jawaban Siswa
            foreach ($session->exam->questions as $question) {
                $maxPossibleScore += $question->score_weight;
                $answer = $session->answers->firstWhere('cbt_question_id', $question->id);

                if (! $answer) {
                    continue;
                }

                if ($question->type === 'multiple_choice') {
                    $selectedOption = $question->options->firstWhere('id', $answer->cbt_option_id);

                    if ($selectedOption && $selectedOption->is_correct) {
                        $scoreGiven = (float) $question->score_weight;
                        $answer->update([
                            'is_correct' => true,
                            'score_given' => $scoreGiven,
                        ]);
                        $totalWeightedScore += $scoreGiven;
                    } else {
                        $answer->update([
                            'is_correct' => false,
                            'score_given' => 0,
                        ]);
                    }
                } elseif ($question->type === 'essay') {
                    // Nilai Essay diambil dari koreksi manual Guru (jika ada)
                    $totalWeightedScore += (float) ($answer->score_given ?? 0);
                }
            }

            // Hitung nilai akhir berbasis persentase (Skala 0 - 100)
            $finalScore = $maxPossibleScore > 0
                ? round(($totalWeightedScore / $maxPossibleScore) * 100, 2)
                : 0;

            // 2. Update Status dan Total Nilai Sesi CBT
            $session->update([
                'total_score' => $finalScore,
                'status' => 'submitted',
                'submitted_at' => $session->submitted_at ?? now(),
            ]);

            // 3. Cari Enrollment Siswa yang Aktif
            $enrollment = Enrollment::where('student_id', $session->student_id)
                ->latest()
                ->first();

            if ($enrollment) {
                $subjectId = $session->exam->subject_id;

                // A. Sinkronkan nilai CBT ke tabel GradeSumatif
                $this->syncToGradeSumatif(
                    $enrollment->id,
                    $subjectId,
                    $sumativeScopeId,
                    $sumativeType,
                    $finalScore
                );

                // B. Hitung Ulang Rata-Rata & Narasi Capaian untuk Raport
                $this->recalculateFinalScore($enrollment->id, $subjectId);
            } else {
                Log::warning("Enrollment tidak ditemukan untuk Student ID: {$session->student_id} saat sinkronisasi CBT.");
            }

            return $finalScore;
        });
    }

    /**
     * Menyimpan/memperbarui data nilai pada GradeSumatif.
     */
    public function syncToGradeSumatif(
        int $enrollmentId,
        int $subjectId,
        ?int $sumativeScopeId,
        string $type,
        float $score
    ): GradeSumatif {
        return GradeSumatif::updateOrCreate(
            [
                'enrollment_id' => $enrollmentId,
                'subject_id' => $subjectId,
                'sumative_scope_id' => $sumativeScopeId,
                'type' => $type,
            ],
            [
                'score' => $score,
            ]
        );
    }

    /**
     * Kalkulasi Ulang Nilai Akhir Raport & Deskripsi Otomatis.
     */
    public function recalculateFinalScore(int $enrollmentId, int $subjectId): GradeFinalScore
    {
        // 1. Rata-Rata Nilai Formatif (Berdasarkan Learning Objective/TP)
        $formativeAvg = GradeFormatif::where('enrollment_id', $enrollmentId)
            ->whereHas('learningObjective', fn ($q) => $q->where('subject_id', $subjectId))
            ->avg('score') ?? 0;

        // 2. Rata-Rata Sumatif Lingkup Materi (SLM)
        $sumativeSlmAvg = GradeSumatif::where('enrollment_id', $enrollmentId)
            ->where('subject_id', $subjectId)
            ->where('type', 'lm')
            ->avg('score') ?? 0;

        // 3. Nilai Sumatif Akhir Semester (SAS/SAT)
        $sumativeSas = GradeSumatif::where('enrollment_id', $enrollmentId)
            ->where('subject_id', $subjectId)
            ->where('type', 'sas')
            ->value('score') ?? 0;

        // 4. Formula Nilai Akhir Kurikulum Merdeka (Bobot standar: Formatif 30%, SLM 40%, SAS 30%)
        $finalScore = round(($formativeAvg * 0.30) + ($sumativeSlmAvg * 0.40) + ($sumativeSas * 0.30), 2);

        // 5. Penentuan Deskripsi Otomatis Capaian Tertinggi & Terendah
        $formativeGrades = GradeFormatif::with('learningObjective')
            ->where('enrollment_id', $enrollmentId)
            ->whereHas('learningObjective', fn ($q) => $q->where('subject_id', $subjectId))
            ->get();

        $highestDescription = null;
        $lowestDescription = null;

        if ($formativeGrades->isNotEmpty()) {
            $highestGrade = $formativeGrades->sortByDesc('score')->first();
            $lowestGrade = $formativeGrades->sortBy('score')->first();

            if ($highestGrade && $highestGrade->learningObjective) {
                $highestDescription = 'Menunjukkan penguasaan yang sangat baik dalam '.$highestGrade->learningObjective->title;
            }

            if ($lowestGrade && $lowestGrade->learningObjective) {
                // Berikan catatan terendah hanya jika skor di bawah ambang batas (misal < 75)
                if ($lowestGrade->score < 75) {
                    $lowestDescription = 'Perlu bimbingan lebih lanjut dalam '.$lowestGrade->learningObjective->title;
                } else {
                    $lowestDescription = 'Menunjukkan penguasaan yang memadai dalam seluruh Tujuan Pembelajaran.';
                }
            }
        }

        // 6. Simpan / Update ke GradeFinalScore
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
                'highest_achieved_description' => $highestDescription,
                'lowest_achieved_description' => $lowestDescription,
            ]
        );
    }
}
