<?php
namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use App\Models\Enrollment;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use App\Models\Student; // Pastikan import Model Student di bagian atas file
use Filament\Forms\Get;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. Tombol Tambah Satuan (Standar Filament)
            Actions\CreateAction::make()
                ->label('Tambah Siswa (Satuan)'),

            // 2. Tombol Plotting Banyak Siswa (Bulk / Multi-Select)

            Actions\Action::make('bulkEnrollment')
                ->label('Plotting Banyak Siswa')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('academic_year_id')
                        ->label('Tahun Ajaran')
                        ->relationship('academicYear', 'year')
                        ->searchable()
                        ->preload()
                        ->live() // Mengaktifkan reaktivitas real-time
                        ->required(),

                    Forms\Components\Select::make('class_id')
                        ->label('Kelas / Rombel Target')
                        ->relationship(
                            name: 'class',
                            titleAttribute: 'name',
                            modifyQueryUsing: function ($query) {
                                $user = auth()->user();
                                $userRole = $user->role ?? $user->role_type ?? '';

                                if (in_array($userRole, ['teacher', 'class_teacher', 'subject_teacher']) && $user->teacher) {
                                    return $query->where('teacher_id', $user->teacher->id);
                                }

                                return $query;
                            }
                        )
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('student_ids')
                        ->label('Pilih Siswa (Bisa Lebih dari 1)')
                        ->options(function (Get $get) {
                            $academicYearId = $get('academic_year_id');

                            // 1. Jika Tahun Ajaran belum dipilih, tampilkan semua siswa
                            if (!$academicYearId) {
                                return Student::query()->pluck('name', 'id');
                            }

                            // 2. Ambil ID siswa yang SUDAH terdaftar di Tahun Ajaran ini
                            $enrolledStudentIds = Enrollment::where('academic_year_id', $academicYearId)
                                ->pluck('student_id')
                                ->toArray();

                            // 3. Tampilkan HANYA siswa yang BELUM terdaftar di kelas manapun pada TA tersebut
                            return Student::query()
                                ->whereNotIn('id', $enrolledStudentIds)
                                ->pluck('name', 'id');
                        })
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Daftar siswa otomatis disaring: hanya menampilkan siswa yang belum masuk ke kelas manapun pada Tahun Ajaran yang dipilih.'),
                ])
                ->action(function (array $data) {
                    $academicYearId = $data['academic_year_id'];
                    $classId = $data['class_id'];
                    $studentIds = $data['student_ids'] ?? [];

                    $insertedCount = 0;
                    $skippedCount = 0;

                    foreach ($studentIds as $studentId) {
                        $exists = Enrollment::where('academic_year_id', $academicYearId)
                            ->where('class_id', $classId)
                            ->where('student_id', $studentId)
                            ->exists();

                        if (!$exists) {
                            Enrollment::create([
                                'academic_year_id' => $academicYearId,
                                'class_id'        => $classId,
                                'student_id'      => $studentId,
                            ]);
                            $insertedCount++;
                        } else {
                            $skippedCount++;
                        }
                    }

                    Notification::make()
                        ->title('Plotting Rombel Selesai')
                        ->body("Berhasil mendaftarkan {$insertedCount} siswa ke kelas. " . ($skippedCount > 0 ? "({$skippedCount} siswa dilewati)." : ""))
                        ->success()
                        ->send();
                }),
            ];
    }
}