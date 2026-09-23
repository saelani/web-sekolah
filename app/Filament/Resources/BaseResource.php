<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

abstract class BaseResource extends Resource
{
    /**
     * 1. HAK AKSES MENU (SIDEBAR)
     */
    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user && in_array($user->role, ['admin', 'headmaster', 'teacher']);
    }

    /**
     * 2. FILTERING DATA SECARA OTOMATIS & FLEKSIBEL
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        /** @var User|null $user */
        $user = auth()->user();

        // Jika yang login bukan teacher (misal admin/headmaster), tampilkan seluruh data
        if (! $user || $user->role !== 'teacher') {
            return $query;
        }

        $model = new (static::getModel());
        $table = $model->getTable();
        $modelClass = get_class($model);

        // Ambil ID teacher dengan aman (mendukung relasi $user->teacher, kolom teacher_id, atau fallback ke user->id)
        $teacherId = $user->teacher?->id ?? $user->teacher_id ?? $user->id;
        $teacher = $user->teacher;
        
        \Illuminate\Support\Facades\Log::info("User Login: {$user->id} ({$user->name}), Role: {$user->role}, TeacherID: {$teacherId}");

        // --- 0. KHUSUS MODEL ACADEMIC CALENDAR (Prioritas Utama ditarik ke atas agar tidak lolos ke commonRelations) ---
        if ($modelClass === \App\Models\AcademicCalendar::class || (Schema::hasColumn($table, 'subject_id') && Schema::hasColumn($table, 'class_room_id'))) {
            if (! $teacherId) {
                return $query->whereRaw('1 = 0');
            }

            // Ambil kombinasi mapel & kelas yang diampu oleh guru ini
            $allowedAssignments = \App\Models\TeacherSubjectClass::where('teacher_id', $teacherId)
                ->orWhere('teacher_id', $user->id) // Cadangan jika relasi menggunakan user_id
                ->get();
            
            $subjectIds = $allowedAssignments->pluck('subject_id')->unique()->toArray();
            $classRoomIds = $allowedAssignments->pluck('class_id')->unique()->toArray();

            return $query->whereIn('subject_id', $subjectIds)
                         ->whereIn('class_room_id', $classRoomIds);
        }

        // --- A. KHUSUS MODEL STUDENT (Siswa) ---
        if ($modelClass === \App\Models\Student::class) {
            if (! $teacher) {
                return $query->whereRaw('1 = 0');
            }

            $homeroomClassIds = method_exists($teacher, 'homeroomClasses') ? $teacher->homeroomClasses()->pluck('id')->toArray() : [];
            $subjectClassIds = method_exists($teacher, 'teacherSubjectClasses') ? $teacher->teacherSubjectClasses()->pluck('class_id')->toArray() : [];
            $allClassIds = array_unique(array_merge($homeroomClassIds, $subjectClassIds));

            return $query->whereIn('class_id', $allClassIds);
        }

        // --- B. KHUSUS MODEL CLASSROOM (Kelas) ---
        if ($modelClass === \App\Models\ClassRoom::class) {
            if (! $teacher) {
                return $query->whereRaw('1 = 0');
            }

            $homeroomClassIds = $query->where('teacher_id', $teacher->id)->pluck('id')->toArray();
            $subjectClassIds = method_exists($teacher, 'teacherSubjectClasses') ? $teacher->teacherSubjectClasses()->pluck('class_id')->toArray() : [];
            $allClassIds = array_unique(array_merge($homeroomClassIds, $subjectClassIds));

            return $query->whereIn('id', $allClassIds);
        }

        // --- C. KHUSUS MODEL MATERIAL (Materi Pembelajaran) ---
        if ($modelClass === \App\Models\Material::class) {
            if (! $teacherId) {
                return $query->whereRaw('1 = 0');
            }

            $subjectNames = \App\Models\TeacherSubjectClass::where('teacher_id', $teacherId)
                ->orWhere('teacher_id', $user->id)
                ->with('subject')
                ->get()
                ->pluck('subject.name')
                ->filter()
                ->unique()
                ->toArray();

            return $query->whereIn('subject', $subjectNames);
        }

        // --- D. CEK KOLOM LANGSUNG DI TABEL UTAMA ---
        if (Schema::hasColumn($table, 'teacher_id')) {
            return $query->where($table.'.teacher_id', $teacherId);
        }
        if (Schema::hasColumn($table, 'user_id')) {
            return $query->where($table.'.user_id', $user->id);
        }

        // --- E. DETEKSI DINAMIS MELALUI RELASI INDUK UMUM ---
        $commonRelations = ['exam', 'cbtExam', 'task', 'quiz', 'assignment', 'material', 'subject', 'learningObjective', 'sumativeScope'];

        foreach ($commonRelations as $relation) {
            if (method_exists($model, $relation)) {
                $relatedModel = $model->{$relation}()->getRelated();
                $relatedTable = $relatedModel->getTable();

                if (Schema::hasColumn($relatedTable, 'teacher_id')) {
                    return $query->whereHas($relation, function ($q) use ($teacherId, $user) {
                        $q->where('teacher_id', $teacherId)->orWhere('teacher_id', $user->id);
                    });
                }

                if ($relation === 'subject' && method_exists($relatedModel, 'teacherSubjectClasses')) {
                    return $query->whereHas('subject', function ($q) use ($teacherId, $user) {
                        $q->whereHas('teacherSubjectClasses', function ($subQ) use ($teacherId, $user) {
                            $subQ->where('teacher_id', $teacherId)->orWhere('teacher_id', $user->id);
                        });
                    });
                }

                if ($relation === 'learningObjective' && method_exists($relatedModel, 'subject')) {
                    return $query->whereHas('learningObjective.subject.teacherSubjectClasses', function ($q) use ($teacherId, $user) {
                        $q->where('teacher_id', $teacherId)->orWhere('teacher_id', $user->id);
                    });
                }

                if ($relation === 'sumativeScope' && method_exists($relatedModel, 'subject')) {
                    return $query->whereHas('sumativeScope.subject.teacherSubjectClasses', function ($q) use ($teacherId, $user) {
                        $q->where('teacher_id', $teacherId)->orWhere('teacher_id', $user->id);
                    });
                }
            }
        }

        return $query;
    }

    /**
     * 3. HAK AKSES MEMBUAT DATA (CREATE)
     */
    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user && in_array($user->role, ['admin', 'teacher']);
    }

    /**
     * 4. HAK AKSES MENGEDIT DATA (EDIT)
     */
    public static function canEdit(Model $record): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (in_array($user->role, ['admin', 'headmaster'])) {
            return true;
        }

        if ($user->role === 'teacher') {
            $teacherId = $user->teacher?->id ?? $user->teacher_id ?? $user->id;
            $modelClass = get_class($record);

            if (isset($record->teacher_id) && ($record->teacher_id === $teacherId || $record->teacher_id === $user->id)) {
                return true;
            }
            if (isset($record->user_id) && $record->user_id === $user->id) {
                return true;
            }

            // Cek khusus Academic Calendar
            if ($modelClass === \App\Models\AcademicCalendar::class) {
                if (! $teacherId) return false;
                return \App\Models\TeacherSubjectClass::where(fn($q) => $q->where('teacher_id', $teacherId)->orWhere('teacher_id', $user->id))
                    ->where('subject_id', $record->subject_id)
                    ->where('class_id', $record->class_room_id)
                    ->exists();
            }

            // Cek khusus Material
            if ($modelClass === \App\Models\Material::class) {
                if (! $teacherId) return false;
                return \App\Models\TeacherSubjectClass::where(fn($q) => $q->where('teacher_id', $teacherId)->orWhere('teacher_id', $user->id))
                    ->whereHas('subject', fn ($q) => $q->where('name', $record->subject))
                    ->exists();
            }

            // Cek secara dinamis melalui relasi induk umum
            $commonRelations = ['exam', 'cbtExam', 'task', 'quiz', 'assignment', 'material'];
            foreach ($commonRelations as $relation) {
                if (method_exists($record, $relation) && $record->{$relation}) {
                    if (isset($record->{$relation}->teacher_id) && ($record->{$relation}->teacher_id === $teacherId || $record->{$relation}->teacher_id === $user->id)) {
                        return true;
                    }
                }
            }

            if (method_exists($record, 'subject') && isset($record->subject_id)) {
                return \App\Models\TeacherSubjectClass::where(fn($q) => $q->where('teacher_id', $teacherId)->orWhere('teacher_id', $user->id))
                    ->where('subject_id', $record->subject_id)
                    ->exists();
            }

            $subjectId = null;
            if (method_exists($record, 'learningObjective') && $record->learningObjective?->subject_id) {
                $subjectId = $record->learningObjective->subject_id;
            } elseif (method_exists($record, 'sumativeScope') && $record->sumativeScope?->subject_id) {
                $subjectId = $record->sumativeScope->subject_id;
            }

            if ($subjectId && $teacherId) {
                return \App\Models\TeacherSubjectClass::where(fn($q) => $q->where('teacher_id', $teacherId)->orWhere('teacher_id', $user->id))
                    ->where('subject_id', $subjectId)
                    ->exists();
            }
        }

        return false;
    }

    /**
     * 5. HAK AKSES MENAMPILKAN DETAIL DATA (VIEW)
     */
    public static function canView(Model $record): bool
    {
        return static::canEdit($record);
    }

    /**
     * 6. HAK AKSES MENGHAPUS DATA (DELETE)
     */
    public static function canDelete(Model $record): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user && $user->role === 'admin';
    }
}