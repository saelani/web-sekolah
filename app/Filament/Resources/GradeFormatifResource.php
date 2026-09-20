<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeFormatifResource\Pages;
use App\Models\Enrollment;
use App\Models\GradeFormatif;
use App\Models\LearningObjective;
use App\Models\Subject;
use App\Models\SumativeScope;
use App\Services\GradeCalculationService;
use App\Traits\HasRoleScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GradeFormatifResource extends Resource
{
    use HasRoleScope;

    protected static ?string $model = GradeFormatif::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Asesmen Intrakurikuler';

    protected static ?string $navigationLabel = 'Nilai Formatif';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Data Penilaian Formatif')
                    ->schema([
                        // 1. Filter Mata Pelajaran
                        Select::make('subject_id')
                            ->label('Mata Pelajaran')
                            ->options(function () {
                                $user = auth()->user();
                                $query = Subject::query();
                                $userRole = $user->role ?? $user->role_type ?? '';

                                if (! in_array($userRole, ['admin', 'headmaster', 'super_admin']) && ! $user->is_admin) {
                                    $teacherId = $user->teacher ? $user->teacher->id : $user->teacher_id;
                                    $query->whereHas('teacherSubjectClasses', function ($q) use ($teacherId) {
                                        $q->where('teacher_id', $teacherId);
                                    });
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(function (Set $set) {
                                $set('learning_objective_id', null);
                                $set('sumative_scope_id', null);
                            })
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        // 2. Pilih Berdasarkan Jenis Penilaian (Bab atau TP)
                        Select::make('assessment_type')
                            ->label('Acuan Penilaian')
                            ->options([
                                'chapter' => 'Penilaian Bab (Lingkup Materi)',
                                'tp' => 'Penilaian Tujuan Pembelajaran (TP)',
                            ])
                            ->default('tp')
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('learning_objective_id', null);
                                $set('sumative_scope_id', null);
                            })
                            ->required(),

                        // 3. Select Bab / Lingkup Materi (Hanya muncul jika dipilih 'chapter')
                        Select::make('sumative_scope_id')
                            ->label('Pilih Bab / Lingkup Materi')
                            ->options(function (Get $get, ?GradeFormatif $record) {
                                $subjectId = $get('subject_id') ?? $record?->subject_id;

                                if (! $subjectId) {
                                    return SumativeScope::pluck('name', 'id');
                                }

                                return SumativeScope::where('subject_id', $subjectId)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn (Get $get) => $get('assessment_type') === 'chapter')
                            ->required(fn (Get $get) => $get('assessment_type') === 'chapter'),

                        // 4. Select Tujuan Pembelajaran / TP (Hanya muncul jika dipilih 'tp')
                        Select::make('learning_objective_id')
                            ->label('Pilih Tujuan Pembelajaran (TP)')
                            ->options(function (Get $get, ?GradeFormatif $record) {
                                $subjectId = $get('subject_id') ?? $record?->learningObjective?->subject_id;

                                if (! $subjectId) {
                                    return [];
                                }

                                return LearningObjective::where('subject_id', $subjectId)
                                    ->get()
                                    ->mapWithKeys(fn ($tp) => [
                                        $tp->id => "[{$tp->code}] ".Str::limit($tp->description, 50),
                                    ]);
                            })
                            ->searchable()
                            ->visible(fn (Get $get) => $get('assessment_type') === 'tp')
                            ->required(fn (Get $get) => $get('assessment_type') === 'tp')
                            ->disabled(fn (Get $get, ?GradeFormatif $record) => ! $get('subject_id') && ! $record),

                        // 5. Select Siswa (Model Enrollment)
                        Select::make('enrollment_id')
                            ->label('Nama Siswa')
                            ->options(function () {
                                return Enrollment::with(['student', 'class'])
                                    ->get()
                                    ->mapWithKeys(function ($enrollment) {
                                        $studentName = optional($enrollment->student)->name ?? 'Siswa Tanpa Nama';
                                        $className = optional($enrollment->class)->name ?? '-';

                                        return [$enrollment->id => "{$studentName} ({$className})"];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        // 6. Input Nilai & Ketuntasan
                        TextInput::make('score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->label('Nilai Formatif (0-100)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('is_achieved', (float) $state >= 70)),

                        Toggle::make('is_achieved')
                            ->label('Tuntas / Achieved')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enrollment.student.name')
                    ->label('Siswa')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('learningObjective.subject.name')
                    ->label('Mata Pelajaran')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('sumativeScope.name')
                    ->label('Bab / Lingkup Materi')
                    ->badge()
                    ->color('warning')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('learningObjective.code')
                    ->label('Kode TP')
                    ->badge()
                    ->color('sky')
                    ->placeholder('-'),

                TextColumn::make('score')
                    ->label('Nilai')
                    ->sortable()
                    ->weight('bold'),

                IconColumn::make('is_achieved')
                    ->boolean()
                    ->label('Ketuntasan'),
            ])
            ->filters([
                SelectFilter::make('subject')
                    ->relationship('learningObjective.subject', 'name')
                    ->label('Filter Mata Pelajaran'),

                SelectFilter::make('sumative_scope_id')
                    ->label('Filter Bab')
                    ->options(SumativeScope::pluck('name', 'id')),
            ])
            ->actions([
                EditAction::make()
                    ->after(function (GradeFormatif $record) {
                        if ($record->learningObjective) {
                            $subjectId = $record->learningObjective->subject_id;
                            GradeCalculationService::calculateForStudent($record->enrollment_id, $subjectId);
                        }
                    }),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGradeFormatifs::route('/'),
            'create' => Pages\CreateGradeFormatif::route('/create'),
            'batch' => Pages\BatchGradeFormatif::route('/batch'),
            'edit' => Pages\EditGradeFormatif::route('/{record}/edit'),
        ];
    }
}
