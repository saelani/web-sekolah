<?php

namespace App\Imports;

use App\Models\Student;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StudentsImport implements ToModel, WithHeadingRow, WithCustomCsvSettings
{
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';'
        ];
    }

    public function model(array $row)
    {
        $dob = null;

        if (!empty($row['dob'])) {
            try {
                // 1. Jika tanggal berupa Serial Number dari Excel (misal: 38580)
                if (is_numeric($row['dob'])) {
                    $dob = Date::excelToDateTimeObject($row['dob'])->format('Y-m-d');
                } else {
                    // 2. Mengubah pemisah "-" atau "." menjadi "/" agar standar
                    $cleanDob = str_replace(['-', '.'], '/', trim($row['dob']));
                    
                    // 3. Gunakan Carbon::parse untuk membaca berbagai format tanggal otomatis
                    $dob = Carbon::parse($cleanDob)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // Jika parsing gagal total, throw Exception agar tahu baris mana yang error
                throw new \Exception("Format tanggal lahir (dob) tidak valid pada NISN {$row['nisn']}: '{$row['dob']}'");
            }
        }

        // Jika dob masih kosong/null setelah diproses, lemparkan error sebelum masuk database
        if (!$dob) {
            throw new \Exception("Tanggal lahir (dob) untuk NISN {$row['nisn']} wajib diisi dan tidak boleh kosong.");
        }

        return Student::updateOrCreate(
            ['nisn' => $row['nisn']],
            [
                'user_id'     => auth()->id(), // Mengaitkan dengan ID user login
                'nis'         => $row['nis'] ?? null,
                'name'        => $row['name'],
                'gender'      => $row['gender'] ?? null,
                'pob'         => $row['pob'] ?? null,
                'dob'         => $dob,
                'address'     => $row['address'] ?? null,
                'parent_name' => $row['parent_name'] ?? null,
                'class_id'    => $row['class_id'] ?? null,
            ]
        );
    }
}