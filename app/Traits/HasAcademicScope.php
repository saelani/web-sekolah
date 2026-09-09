<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasAcademicScope
{
    /**
     * Terapkan scope global berdasarkan role user & relasi akademis.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Jika User tidak login atau merupakan Admin, tampilkan semua data
        if (!$user || $user->hasRole('admin') || $user->is_admin) {
            return $query;
        }

        $teacherId = $user->teacher_id ?? null;

        if (!$teacherId) {
            // Jika user BUKAN admin dan BUKAN guru, kembalikan query kosong
            return $query->whereRaw('1 = 0');
        }

        // Ambil nama tabel secara aman dari Builder
        $tableName = $query->getModel()->getTable();

        return match ($tableName) {
            // Filter Guru
            'acad_teachers' => $query->where('id', $teacherId),

            // Filter Pengampu Kelas & Subject
            'acad_teacher_subject_classe' => $query->where('teacher_id', $teacherId),

            // Filter Kelas yang Diampu
            'acad_classes' => $query->whereIn('id', function ($q) use ($teacherId) {
                $q->select('class_id')
                  ->from('acad_teacher_subject_classe')
                  ->where('teacher_id', $teacherId);
            }),

            // Filter Mata Pelajaran yang Diajar
            'acad_subjects' => $query->whereIn('id', function ($q) use ($teacherId) {
                $q->select('subject_id')
                  ->from('acad_teacher_subject_classe')
                  ->where('teacher_id', $teacherId);
            }),

            // Filter Siswa (Hanya siswa di kelas yang diampu guru)
            'acad_students' => $query->whereIn('id', function ($q) use ($teacherId) {
                $q->select('student_id')
                  ->from('acad_enrollments')
                  ->whereIn('class_id', function ($q2) use ($teacherId) {
                      $q2->select('class_id')
                         ->from('acad_teacher_subject_classe')
                         ->where('teacher_id', $teacherId);
                  });
            }),

            // Filter Enrollment/Pendaftaran Siswa
            'acad_enrollments' => $query->whereIn('class_id', function ($q) use ($teacherId) {
                $q->select('class_id')
                  ->from('acad_teacher_subject_classe')
                  ->where('teacher_id', $teacherId);
            }),

            default => $query,
        };
    }
}