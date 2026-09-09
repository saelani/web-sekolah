<?php
namespace App\Filament\Resources\StudentAttendanceResource\Pages;

use App\Filament\Resources\StudentAttendanceResource;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\StudentAttendance;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class BatchStudentAttendance extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = StudentAttendanceResource::class;

    protected static string $view = 'filament.resources.student-attendance-resource.pages.batch-student-attendance';

    protected static ?string $title = 'Input Presensi Ketidakhadiran Siswa';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'date' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Parameter Presensi')
                    ->description('Hanya siswa yang Sakit, Izin, atau Alpa yang akan disimpan ke basis data.')
                    ->schema([
                        Select::make('class_id')
                            ->label('Kelas')
                            ->options(function () {
                                $user = Auth::user();
                                $query = ClassRoom::query();
                                $userRole = $user->role ?? $user->role_type ?? '';

                                if (!in_array($userRole, ['admin', 'headmaster', 'super_admin']) && !($user->is_admin ?? false)) {
                                    $teacherId = $user->teacher?->id ?? $user->teacher_id;
                                    if ($teacherId) {
                                        $query->where('teacher_id', $teacherId);
                                    } else {
                                        return [];
                                    }
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->loadStudentsData($get, $set))
                            ->required(),

                        DatePicker::make('date')
                            ->label('Tanggal Presensi')
                            ->default(now())
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->loadStudentsData($get, $set))
                            ->required(),
                    ])->columns(2),

                Section::make('Daftar Ketidakhadiran Siswa')
                    ->schema([
                        Repeater::make('students')
                            ->label('Daftar Siswa')
                            ->itemLabel(fn (array $state): ?string => $state['student_name'] ?? 'Siswa')
                            ->collapsible()
                            ->grid([
                                'default' => 1,
                                'md'      => 2,
                            ])
                            ->schema([
                                Hidden::make('student_id'),

                                Select::make('status')
                                    ->label('Status Kehadiran')
                                    ->options([
                                        'Hadir' => 'Hadir (Tidak Disimpan)',
                                        'Sakit' => 'Sakit',
                                        'Izin'  => 'Izin',
                                        'Alpa'  => 'Alpa (Tanpa Keterangan)',
                                    ])
                                    ->default('Hadir')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('notes')
                                    ->label('Catatan Opsional')
                                    ->placeholder('Misal: Surat Dokter Ada')
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->extraAttributes([
                                'class' => '[&_.fi-fo-repeater-item]:p-3 [&_.fi-fo-repeater-item-header]:py-1 [&_.fi-fo-repeater-item-header]:px-2 [&_.fi-fo-repeater-item-content]:p-2',
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function loadStudentsData(Get $get, Set $set): void
    {
        $classId = $get('class_id');
        $date = $get('date');

        if (!$classId || !$date) {
            $set('students', []);
            return;
        }

        $students = Student::where('class_id', $classId)
            ->orderBy('name')
            ->get();

        $studentsData = $students->map(function ($student) use ($classId, $date) {
            $existingAttendance = StudentAttendance::where('class_id', $classId)
                ->where('student_id', $student->id)
                ->whereDate('date', $date)
                ->first();

            return [
                'student_id'   => $student->id,
                'student_name' => $student->name,
                'status'       => $existingAttendance?->status ?? 'Hadir',
                'notes'        => $existingAttendance?->notes ?? null,
            ];
        })->toArray();

        $set('students', $studentsData);
    }

    public function save(): void
    {
        $formData = $this->form->getState();

        $classId = $formData['class_id'] ?? null;
        $date = $formData['date'] ?? now()->format('Y-m-d');
        $students = $formData['students'] ?? [];
        $userId = Auth::id();

        if (!$classId || empty($students)) {
            Notification::make()
                ->title('Gagal Menyimpan')
                ->body('Pilih kelas dan siswa terlebih dahulu.')
                ->danger()
                ->send();
            return;
        }

        $savedCount = 0;
        $deletedCount = 0;

        // Mengecek apakah kolom user_id ada di database
        $hasUserIdColumn = Schema::hasColumn('ops_student_attendances', 'user_id');

        foreach ($students as $student) {
            $status = $student['status'] ?? 'Hadir';

            if ($status === 'Hadir') {
                // Jika diset Hadir, hapus data ketidakhadiran dari DB
                $deleted = StudentAttendance::where('class_id', $classId)
                    ->where('student_id', $student['student_id'])
                    ->whereDate('date', $date)
                    ->delete();

                if ($deleted) {
                    $deletedCount++;
                }
            } else {
                // Siapkan data payload
                $payload = [
                    'status' => $status,
                    'notes'  => $student['notes'] ?? null,
                ];

                if ($hasUserIdColumn) {
                    $payload['user_id'] = $userId;
                }

                // Simpan atau update data Sakit / Izin / Alpa
                StudentAttendance::updateOrCreate(
                    [
                        'class_id'   => $classId,
                        'student_id' => $student['student_id'],
                        'date'       => $date,
                    ],
                    $payload
                );
                $savedCount++;
            }
        }

        Notification::make()
            ->title('Berhasil Disimpan')
            ->body("Selesai memproses presensi: {$savedCount} data ketidakhadiran disimpan, {$deletedCount} data dikembalikan ke Hadir.")
            ->success()
            ->send();
    }
}