<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicCalendarResource\Pages;
use App\Models\AcademicCalendar;
use App\Models\LearningObjective;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AcademicCalendarResource extends BaseResource
{
    protected static ?string $model = AcademicCalendar::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Akademik SD';

    protected static ?string $navigationLabel = 'Kalender Pembelajaran';

    protected static ?int $navigationSort = 4;

    /**
     * Override Query Scope agar Guru hanya melihat kalender dari mapel & kelas yang diampu
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        if ($user->role === 'teacher') {
            $teacherId = $user->teacher?->id ?? $user->teacher_id ?? $user->id;

            $assignments = TeacherSubjectClass::where('teacher_id', $teacherId)
                ->orWhere('teacher_id', $user->id)
                ->get();

            $subjectIds = $assignments->pluck('subject_id')->unique()->toArray();
            $classRoomIds = $assignments->pluck('class_id')->unique()->toArray();

            if (empty($subjectIds) || empty($classRoomIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('subject_id', $subjectIds)
                         ->whereIn('class_room_id', $classRoomIds);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Rencana & Alokasi Waktu Pembelajaran')
                    ->headerActions([
                        Forms\Components\Actions\Action::make('reset_form')
                            ->label('Reset Form')
                            ->color('gray')
                            ->icon('heroicon-o-arrow-path')
                            ->requiresConfirmation()
                            ->modalHeading('Reset Formulir?')
                            ->modalDescription('Semua pilihan kelas, mapel, dan inputan lainnya akan dikosongkan.')
                            ->action(function (Set $set) {
                                session()->forget(['last_class_room_id', 'last_subject_id', 'last_activity_type']);
                                $set('class_room_id', null);
                                $set('subject_id', null);
                                $set('activity_type', 'kbm');
                                $set('learning_objective_id', null);
                                $set('start_date', now()->format('Y-m-d'));
                                $set('end_date', now()->format('Y-m-d'));
                                $set('start_period', 1);
                                $set('total_jp', 2);
                                $set('title', null);
                                $set('notes', null);
                                $set('time_info', null);
                            }),
                    ])
                    ->schema([
                        Forms\Components\Select::make('class_room_id')
                            ->relationship('classRoom', 'name')
                            ->label('Kelas')
                            ->required()
                            ->live()
                            ->default(fn () => session('last_class_room_id'))
                            ->afterStateUpdated(fn ($state) => session(['last_class_room_id' => $state])),

                        // --- PILIHAN MAPEL DISESUAIKAN BERDASARKAN GURU YANG MENGAMPU ---
                        Forms\Components\Select::make('subject_id')
                            ->label('Mata Pelajaran')
                            ->options(function () {
                                $user = auth()->user();
                                if ($user->role === 'teacher') {
                                    $teacherId = $user->teacher?->id ?? $user->teacher_id ?? $user->id;
                                    $subjectIds = TeacherSubjectClass::where('teacher_id', $teacherId)
                                        ->orWhere('teacher_id', $user->id)
                                        ->pluck('subject_id');

                                    return Subject::whereIn('id', $subjectIds)->pluck('name', 'id');
                                }
                                return Subject::pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->default(fn () => session('last_subject_id'))
                            ->afterStateUpdated(fn ($state) => session(['last_subject_id' => $state])),

                        Forms\Components\Select::make('activity_type')
                            ->label('Jenis Kegiatan')
                            ->options([
                                'kbm' => 'KBM Harian (TP)',
                                'slm' => 'Sumatif Lingkup Materi (SLM)',
                                'sls' => 'Sumatif Akhir Semester (SLS)',
                                'event' => 'Kegiatan Sekolah / Projek P5',
                                'libur' => 'Libur / Non-KBM',
                            ])
                            ->default(fn () => session('last_activity_type', 'kbm'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state) => session(['last_activity_type' => $state])),

                        Forms\Components\Select::make('learning_objective_id')
                            ->label('Target TP / Bab')
                            ->options(function (Get $get) {
                                $subjectId = $get('subject_id');
                                if (! $subjectId) {
                                    return [];
                                }

                                return LearningObjective::where('subject_id', $subjectId)
                                    ->get()
                                    ->pluck('full_title', 'id');
                            })
                            ->searchable()
                            ->visible(fn (Get $get) => in_array($get('activity_type'), ['kbm', 'slm']))
                            ->nullable(),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(now())
                            ->live()
                            ->afterStateUpdated(fn ($state, Set $set) => $set('end_date', $state)),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->default(now())
                            ->minDate(fn (Get $get) => $get('start_date')),

                        Forms\Components\Select::make('start_period')
                            ->label('Mulai Jam Ke-')
                            ->options([
                                1 => 'Jam ke-1 (06:30)',
                                2 => 'Jam ke-2 (07:05)',
                                3 => 'Jam ke-3 (07:40)',
                                4 => 'Jam ke-4 (08:15)',
                                5 => 'Jam ke-5 (09:05 - set Istirahat 1)',
                                6 => 'Jam ke-6 (09:40)',
                                7 => 'Jam ke-7 (10:15)',
                                8 => 'Jam ke-8 (11:05 - set Istirahat 2)',
                                9 => 'Jam ke-9 (11:40)',
                            ])
                            ->default(1)
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculatePeriodDuration($get, $set)),

                        Forms\Components\TextInput::make('total_jp')
                            ->label('Jumlah JP ( @ 35 Menit )')
                            ->numeric()
                            ->default(2)
                            ->placeholder('Contoh: 2')
                            ->minValue(1)
                            ->maxValue(9)
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculatePeriodDuration($get, $set)),

                        Forms\Components\Placeholder::make('time_info')
                            ->label('Keterangan Durasi Jam')
                            ->content(function (Get $get, Set $set) {
                                self::calculatePeriodDuration($get, $set);

                                return $get('time_info') ?? 'Pilih Jam Ke- dan Jumlah JP untuk melihat estimasi waktu.';
                            }),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul / Agenda Pembelajaran')
                            ->placeholder('Contoh: Pembahasan Bab 3 - Pancasila')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Tambahan / Catatan Jurnal')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    protected static function calculatePeriodDuration(Get $get, Set $set): void
    {
        $startPeriod = (int) $get('start_period');
        $jp = (int) $get('total_jp');

        if (! $startPeriod || ! $jp) {
            return;
        }

        $scheduleMaster = [
            1 => ['start' => '06:30', 'end' => '07:05'],
            2 => ['start' => '07:05', 'end' => '07:40'],
            3 => ['start' => '07:40', 'end' => '08:15'],
            4 => ['start' => '08:15', 'end' => '08:50'],
            5 => ['start' => '09:05', 'end' => '09:40'],
            6 => ['start' => '09:40', 'end' => '10:15'],
            7 => ['start' => '10:15', 'end' => '10:50'],
            8 => ['start' => '11:05', 'end' => '11:40'],
            9 => ['start' => '11:40', 'end' => '12:15'],
        ];

        $endPeriod = $startPeriod + $jp - 1;

        if (isset($scheduleMaster[$startPeriod])) {
            $startTime = $scheduleMaster[$startPeriod]['start'];

            if (isset($scheduleMaster[$endPeriod])) {
                $endTime = $scheduleMaster[$endPeriod]['end'];
            } else {
                $endTime = Carbon::parse($startTime)->addMinutes($jp * 35)->format('H:i');
            }

            $labelPeriod = ($startPeriod == $endPeriod)
                ? "Jam Ke-{$startPeriod}"
                : "Jam Ke-{$startPeriod} s/d {$endPeriod}";

            $set('time_info', "⏱️ {$labelPeriod} | Waktu: {$startTime} - {$endTime} WIB ({$jp} JP)");
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Pelaksanaan')
                    ->formatStateUsing(function ($record) {
                        $start = Carbon::parse($record->start_date);
                        $end = Carbon::parse($record->end_date);

                        if ($start->equalTo($end)) {
                            return $start->format('d M Y');
                        }

                        return $start->format('d M').' - '.$end->format('d M Y');
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('classRoom.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Mapel')
                    ->sortable(),

                Tables\Columns\TextColumn::make('activity_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'kbm' => 'success',
                        'slm' => 'warning',
                        'sls' => 'danger',
                        'event' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                Tables\Columns\TextColumn::make('learningObjective.description')
                    ->label('Tujuan Pembelajaran (TP)')
                    ->wrap()
                    ->placeholder('-')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Filter Mata Pelajaran')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user->role === 'teacher') {
                            $teacherId = $user->teacher?->id ?? $user->teacher_id ?? $user->id;
                            $subjectIds = TeacherSubjectClass::where('teacher_id', $teacherId)
                                ->orWhere('teacher_id', $user->id)
                                ->pluck('subject_id');

                            return Subject::whereIn('id', $subjectIds)->pluck('name', 'id');
                        }
                        return Subject::pluck('name', 'id');
                    }),
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademicCalendars::route('/'),
            'create' => Pages\CreateAcademicCalendar::route('/create'),
            'edit' => Pages\EditAcademicCalendar::route('/{record}/edit'),
        ];
    }
}