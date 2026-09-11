<?php

namespace App\Filament\Resources\GradeP5ScoreResource\Pages;

use App\Filament\Resources\GradeP5ScoreResource;
use App\Models\ClassRoom;
use App\Models\Enrollment;
use App\Models\GradeP5Score;
use App\Models\P5Project;
use App\Models\P5Subelement;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class BatchGradeP5Score extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = GradeP5ScoreResource::class;

    protected static string $view = 'filament.resources.grade-p5-score-resource.pages.batch-grade-p5-score';

    protected static ?string $title = 'Input Batch Nilai P5';

    public ?array $filterData = [];
    public ?array $scoresData = [];

    public function mount(): void
    {
        $this->filterForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'filterForm',
            'scoresForm',
        ];
    }

    /**
     * Form Filter
     */
    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Filter Penilaian P5')
                    ->schema([
                        Forms\Components\Select::make('p5_project_id')
                            ->label('Projek P5')
                            ->options(P5Project::pluck('title', 'id'))
                            ->live()
                            ->required()
                            ->afterStateUpdated(fn () => $this->reset('scoresData')),

                        Forms\Components\Select::make('p5_subelement_id')
                            ->label('Sub-elemen P5')
                            ->options(function (Get $get) {
                                $projectId = $get('p5_project_id');
                                if (!$projectId) return [];

                                return P5Subelement::where('p5_project_id', $projectId)
                                    ->pluck('subelement_name', 'id');
                            })
                            ->live()
                            ->required()
                            ->afterStateUpdated(fn () => $this->loadStudents()),

                        Forms\Components\Select::make('class_id') // Menggunakan class_id
                            ->label('Kelas')
                            ->options(ClassRoom::pluck('name', 'id'))
                            ->live()
                            ->required()
                            ->afterStateUpdated(fn () => $this->loadStudents()),
                    ])->columns(3),
            ])
            ->statePath('filterData');
    }

    /**
     * Form Matriks Input Nilai per Siswa
     */
    public function scoresForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Daftar Siswa & Capaian P5')
                    ->schema([
                        Forms\Components\Repeater::make('scores')
                            ->label('Nilai Siswa')
                            ->schema([
                                Forms\Components\Hidden::make('enrollment_id'),

                                Forms\Components\TextInput::make('student_name')
                                    ->label('Nama Siswa')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(2),

                                Forms\Components\Select::make('score')
                                    ->label('Capaian P5')
                                    ->options([
                                        'BB'  => 'Belum Berkembang (BB)',
                                        'MB'  => 'Mulai Berkembang (MB)',
                                        'BSH' => 'Berkembang Sesuai Harapan (BSH)',
                                        'SB'  => 'Sangat Berkembang (SB)',
                                    ])
                                    ->required()
                                    ->columnSpan(2),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),
            ])
            ->statePath('scoresData');
    }

    /**
     * Load Data Siswa berdasarkan class_id & Prefill Nilai P5
     */
    public function loadStudents(): void
    {
        $classId = $this->filterData['class_id'] ?? null;
        $subelementId = $this->filterData['p5_subelement_id'] ?? null;

        if (!$classId || !$subelementId) {
            $this->scoresData = ['scores' => []];
            return;
        }

        // Ambil pendaftaran siswa aktif menggunakan kolom class_id
        $enrollments = Enrollment::with('student')
            ->where('class_id', $classId)
            ->get();

        // Ambil nilai yang sudah pernah diinput sebelumnya
        $existingScores = GradeP5Score::where('p5_subelement_id', $subelementId)
            ->whereIn('enrollment_id', $enrollments->pluck('id'))
            ->pluck('score', 'enrollment_id')
            ->toArray();

        $scoresList = [];
        foreach ($enrollments as $enrollment) {
            $scoresList[] = [
                'enrollment_id' => $enrollment->id,
                'student_name'  => $enrollment->student->name ?? '-',
                'score'         => $existingScores[$enrollment->id] ?? 'BSH',
            ];
        }

        $this->scoresForm->fill([
            'scores' => $scoresList,
        ]);
    }

    /**
     * Simpan Semua Nilai Batch
     */
    public function save(): void
    {
        $filter = $this->filterForm->getState();
        $scoresState = $this->scoresForm->getState();

        $subelementId = $filter['p5_subelement_id'];

        foreach ($scoresState['scores'] as $item) {
            GradeP5Score::updateOrCreate(
                [
                    'enrollment_id'    => $item['enrollment_id'],
                    'p5_subelement_id' => $subelementId,
                ],
                [
                    'score' => $item['score'],
                ]
            );
        }

        Notification::make()
            ->title('Berhasil Menyimpan Nilai P5!')
            ->success()
            ->send();
        
        $this->redirect(GradeP5ScoreResource::getUrl('index'));
    }
}