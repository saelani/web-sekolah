<?php

namespace App\Imports;

use App\Models\CbtQuestion;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CbtQuestionImport implements ToModel, WithHeadingRow
{
    protected $cbtExamId;

    public function __construct($cbtExamId)
    {
        $this->cbtExamId = $cbtExamId;
    }

    public function model(array $row)
    {
        // 1. Ambil data dari CSV/Excel
        $questionText = $row['pertanyaan'] ?? '';
        $type = strtolower(trim($row['tipe'] ?? 'multiple_choice'));
        $weight = $row['bobot'] ?? 1;
        $kunciJawaban = strtoupper(trim($row['kunci_jawaban'] ?? ''));

        if (empty($questionText)) {
            return null;
        }

        // 2. Buat Record Soal (CbtQuestion)
        $question = CbtQuestion::create([
            'cbt_exam_id' => $this->cbtExamId,
            'question_text' => $questionText,
            'type' => $type,
            'score_weight' => $weight,
        ]);

        // 3. Jika Pilihan Ganda, tambahkan Opsi Jawaban lewat relasi Eloquent
        if ($type === 'multiple_choice') {
            $options = [
                'A' => $row['opsi_a'] ?? null,
                'B' => $row['opsi_b'] ?? null,
                'C' => $row['opsi_c'] ?? null,
                'D' => $row['opsi_d'] ?? null,
                'E' => $row['opsi_e'] ?? null,
            ];

            foreach ($options as $key => $optionText) {
                // Abaikan jika opsi kosong
                if (is_null($optionText) || $optionText === '') {
                    continue;
                }

                // Menggunakan relasi $question->options() agar otomatis menggunakan model yang tepat
                $question->options()->create([
                    'option_text' => (string) $optionText,
                    'is_correct' => ($key === $kunciJawaban),
                ]);
            }
        }

        return $question;
    }
}
