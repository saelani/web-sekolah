<?php

namespace App\Filament\Resources\GradeFormatifResource\Pages;

use App\Filament\Resources\GradeFormatifResource;
use App\Models\ClassRoom;
use App\Models\Enrollment;
use App\Models\GradeFormatif;
use App\Models\LearningObjective;
use App\Models\Subject;
use App\Services\GradeCalculationService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

class BatchGradeFormatif extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = GradeFormatifResource::class;

    protected static string $view = 'filament.resources.grade-formatif-resource.pages.batch-grade-formatif';

    protected static ?string $title = 'Input Nilai Massal (Formatif, SLM & SLS)';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter & Parameter Penilaian')
                    ->schema([
                        // 1. Jenis Penilaian (Formatif TP / Sumatif SLM / Sumatif SLS)
                        Select::make('assessment_type')
                            ->label('Jenis Penilaian')
                            ->options([
                                'formatif' => 'Formatif (Tujuan Pembelajaran)',
                                'slm'      => 'Sumatif Lingkup Materi (SLM)',
                                'sls'      => 'Sumatif Akhir Semester (SLS)',
                            ])
                            ->default('formatif')
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('learning_objective_id', null);
                                $set('students', []);
                            })
                            ->required(),

                        // 2. Pilih Kelas
                        Select::make('class_id')
                            ->label('Kelas')
                            ->options(function () {
                                $user = auth()->user();
                                $query = ClassRoom::query();
                                $userRole = $user->role ?? $user->role_type ?? '';

                                if (!in_array($userRole, ['admin', 'headmaster', 'super_admin']) && !($user->is_admin ?? false)) {
                                    $teacherId = $user->teacher?->id ?? $user->teacher_id;

                                    if ($teacherId) {
                                        $query->whereHas('teacherSubjectClasses', function ($q) use ($teacherId) {
                                            $q->where('teacher_id', $teacherId);
                                        });
                                    } else {
                                        return [];
                                    }
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('subject_id', null);
                                $set('learning_objective_id', null);
                                $set('students', []);
                            })
                            ->required(),

                        // 3. Pilih Mata Pelajaran
                        Select::make('subject_id')
                            ->label('Mata Pelajaran')
                            ->options(function (Get $get) {
                                $classId = $get('class_id');
                                if (!$classId) return [];

                                $user = auth()->user();
                                $query = Subject::query();
                                $userRole = $user->role ?? $user->role_type ?? '';

                                if (!in_array($userRole, ['admin', 'headmaster', 'super_admin']) && !($user->is_admin ?? false)) {
                                    $teacherId = $user->teacher?->id ?? $user->teacher_id;

                                    if ($teacherId) {
                                        $query->whereHas('teacherSubjectClasses', function ($q) use ($teacherId, $classId) {
                                            $q->where('teacher_id', $teacherId)->where('class_id', $classId);
                                        });
                                    } else {
                                        return [];
                                    }
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('learning_objective_id', null);
                                $set('students', []);
                            })
                            ->disabled(fn (Get $get) => !$get('class_id'))
                            ->required(),

                        // 4. Pilih Tujuan Pembelajaran (TP) / Lingkup Materi
                        Select::make('learning_objective_id')
                            ->label(fn (Get $get) => $get('assessment_type') === 'slm' ? 'Lingkup Materi (TP)' : 'Tujuan Pembelajaran (TP)')
                            ->options(function (Get $get) {
                                $subjectId = $get('subject_id');
                                if (!$subjectId) return [];

                                return LearningObjective::where('subject_id', $subjectId)
                                    ->get()
                                    ->mapWithKeys(fn ($tp) => [
                                        $tp->id => "[{$tp->code}] " . Str::limit($tp->description, 60)
                                    ]);
                            })
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->loadStudentsData($get, $set))
                            ->visible(fn (Get $get) => in_array($get('assessment_type'), ['formatif', 'slm']))
                            ->disabled(fn (Get $get) => !$get('subject_id'))
                            ->required(fn (Get $get) => in_array($get('assessment_type'), ['formatif', 'slm'])),

                    ])->columns(4),

                Section::make('Daftar Input Nilai Siswa')
                    ->schema([
                        Repeater::make('students')
                            ->label('Daftar Siswa')
                            ->itemLabel(fn (array $state): ?string => $state['student_name'] ?? 'Siswa')
                            ->collapsible()
                            ->grid([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->schema([
                                Hidden::make('enrollment_id'),

                                TextInput::make('score')
                                    ->label('Nilai (0-100)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->placeholder('Def: 68')
                                    ->inlineLabel()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state !== null && $state !== '') {
                                            $set('is_achieved', (float)$state >= 70);
                                        }
                                    })
                                    ->columnSpan(1),

                                Toggle::make('is_achieved')
                                    ->label('Tuntas?')
                                    ->default(true)
                                    ->inline(true)
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

    /**
     * Memuat daftar siswa dan nilai yang tersimpan berdasarkan jenis penilaian
     */
    protected function loadStudentsData(Get $get, Set $set): void
    {
        $classId = $get('class_id');
        $type = $get('assessment_type');
        $tpId = $get('learning_objective_id');

        if (!$classId) {
            $set('students', []);
            return;
        }

        if (in_array($type, ['formatif', 'slm']) && !$tpId) {
            $set('students', []);
            return;
        }

        $enrollments = Enrollment::with('student')
            ->where('class_id', $classId)
            ->get();

        $studentsData = $enrollments->map(function ($enrollment) use ($type, $tpId) {
            $existingGrade = null;

            if ($type === 'formatif') {
                $existingGrade = GradeFormatif::where('enrollment_id', $enrollment->id)
                    ->where('learning_objective_id', $tpId)
                    ->first();
            } else if ($type === 'slm') {
                // Pastikan kolom / model penampung nilai SLM disesuaikan (misal: grade_slm / kolom type)
                $existingGrade = GradeFormatif::where('enrollment_id', $enrollment->id)
                    ->where('learning_objective_id', $tpId)
                    ->where('type', 'slm')
                    ->first();
            } else if ($type === 'sls') {
                // Nilai Akhir Semester (SLS) terikat pada enrollment & subject
                $existingGrade = GradeFormatif::where('enrollment_id', $enrollment->id)
                    ->where('type', 'sls')
                    ->first();
            }

            return [
                'enrollment_id' => $enrollment->id,
                'student_name'  => $enrollment->student->name ?? 'Siswa Tanpa Nama',
                'score'         => $existingGrade?->score ?? null,
                'is_achieved'   => $existingGrade?->is_achieved ?? true,
            ];
        })->toArray();

        $set('students', $studentsData);
    }

    public function save(): void
    {
        $formData = $this->form->getState();

        $type = $formData['assessment_type'] ?? 'formatif';
        $tpId = $formData['learning_objective_id'] ?? null;
        $subjectId = $formData['subject_id'] ?? null;
        $students = $formData['students'] ?? [];

        if (empty($students)) {
            Notification::make()
                ->title('Gagal Menyimpan')
                ->body('Data siswa tidak ditemukan.')
                ->danger()
                ->send();
            return;
        }

        foreach ($students as $student) {
            $hasScore = $student['score'] !== null && $student['score'] !== '';
            $score = $hasScore ? (float)$student['score'] : 68;
            $isAchieved = $hasScore ? (bool)$student['is_achieved'] : ($score >= 70);

            // Simpan / Perbarui nilai berdasarkan jenis penilaian
            GradeFormatif::updateOrCreate(
                [
                    'enrollment_id' => $student['enrollment_id'],
                    'learning_objective_id' => in_array($type, ['formatif', 'slm']) ? $tpId : null,
                    'type' => $type,
                ],
                [
                    'score' => $score,
                    'is_achieved' => $isAchieved,
                ]
            );

            // Hitung ulang nilai akhir semester di service
            if (class_exists(GradeCalculationService::class)) {
                GradeCalculationService::calculateForStudent($student['enrollment_id'], $subjectId);
            }
        }

        Notification::make()
            ->title('Berhasil Disimpan')
            ->body("Seluruh nilai {$type} berhasil diperbarui.")
            ->success()
            ->send();
    }
}