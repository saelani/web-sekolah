<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait HasRoleScope
{
    /**
     * Helper internal untuk mengambil daftar role user dari berbagai struktur DB/relasi
     */
    protected static function getUserRoles($user): array
    {
        if (!$user) return [];

        $roles = [];

        // 1. Cek relasi many-to-many / HasMany (misal: role_user)
        if (method_exists($user, 'roles') && $user->roles()->exists()) {
            $roles = array_merge($roles, $user->roles->pluck('role')->toArray());
        }

        // 2. Cek relasi BelongsTo / HasOne ($user->role)
        if (is_object($user->role) && isset($user->role->role)) {
            $roles[] = $user->role->role;
        } elseif (is_string($user->role) && !empty($user->role)) {
            $roles[] = $user->role;
        }

        // 3. Cek kolom role_type
        if (!empty($user->role_type)) {
            $roles[] = $user->role_type;
        }

        return array_unique($roles);
    }

    /**
     * Helper static untuk memfilter query kustom di Filament Form / Actions
     */
    public static function applyRoleScope(Builder $query, string $column = 'teacher_id'): Builder
    {
        $user = auth()->user();

        if (!$user) {
            return $query;
        }

        $roles = static::getUserRoles($user);

        // Admin / Headmaster / Super Admin: Akses Penuh
        if (array_intersect($roles, ['admin', 'headmaster', 'super_admin']) || ($user->is_admin ?? false)) {
            return $query;
        }

        // Guru
        if (array_intersect($roles, ['teacher', 'class_teacher', 'subject_teacher'])) {
            $teacherId = $user->teacher?->id ?? $user->teacher_id;
            if ($teacherId) {
                return $query->where($column, $teacherId);
            }
        }

        return $query;
    }

    /**
     * Override query Filament Resource secara otomatis berdasarkan role user
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (!$user) {
            return $query;
        }

        $roles = static::getUserRoles($user);

        // 1. Admin, Super Admin, & Kepala Sekolah: Akses Penuh
        if (array_intersect($roles, ['admin', 'headmaster', 'super_admin']) || ($user->is_admin ?? false)) {
            return $query;
        }

        $modelInstance = new static::$model;
        $table = $modelInstance->getTable();

        // 2. Jika Guru (Teacher / Class Teacher / Subject Teacher)
        if (array_intersect($roles, ['teacher', 'class_teacher', 'subject_teacher'])) {
            
            // Khusus 'acad_teachers' (Guru hanya bisa lihat profilnya sendiri)
            if ($table === 'acad_teachers') {
                return $query->where($table . '.user_id', $user->id);
            }

            // Khusus 'acad_subjects' (Mapel): Guru bisa lihat semua mapel
            if ($table === 'acad_subjects') {
                return $query;
            }

            // Khusus 'acad_classes': Tampilkan kelas tempat guru jadi Wali Kelas atau Guru Pengampu Mapel
            if ($table === 'acad_classes' && $user->teacher) {
                return $query->where(function ($q) use ($user, $table) {
                    $q->where($table . '.teacher_id', $user->teacher->id)
                      ->orWhereHas('teacherSubjectClasses', function ($ts) use ($user) {
                          $ts->where('teacher_id', $user->teacher->id);
                      });
                });
            }

            // Untuk tabel Penilaian yang menggunakan enrollment_id
            if (str_starts_with($table, 'grade_') && Schema::hasColumn($table, 'enrollment_id') && $user->teacher) {
                return $query->whereHas('enrollment.class.teacherSubjectClasses', function ($q) use ($user) {
                    $q->where('teacher_id', $user->teacher->id);
                });
            }

            // Fallback untuk kolom teacher_id / user_id
            if ($user->teacher && Schema::hasColumn($table, 'teacher_id')) {
                return $query->where($table . '.teacher_id', $user->teacher->id);
            }

            if (Schema::hasColumn($table, 'user_id')) {
                return $query->where($table . '.user_id', $user->id);
            }
        }

        // 3. Jika Siswa (Student)
        if (in_array('student', $roles) && $user->student) {
            if ($table === 'acad_students') {
                return $query->where($table . '.id', $user->student->id);
            }

            if (Schema::hasColumn($table, 'student_id')) {
                return $query->where($table . '.student_id', $user->student->id);
            }

            if (Schema::hasColumn($table, 'enrollment_id')) {
                return $query->whereHas('enrollment', function ($q) use ($user) {
                    $q->where('student_id', $user->student->id);
                });
            }
        }

        return $query;
    }
}