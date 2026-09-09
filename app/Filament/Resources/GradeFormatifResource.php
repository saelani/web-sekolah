<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeFormatifResource\Pages;
use App\Models\GradeFormatif;
use App\Models\LearningObjective;
use App\Models\Enrollment; // Gunakan model Enrollment
use App\Models\Subject;
use App\Services\GradeCalculationService;
use App\Traits\HasRoleScope;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

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

                                if (!in_array($userRole, ['admin', 'headmaster', 'super_admin']) && !$user->is_admin) {
                                    $teacherId = $user->teacher ? $user->teacher->id : $user->teacher_id;
                                    $query->whereHas('teacherSubjectClasses', function ($q) use ($teacherId) {
                                        $q->where('teacher_id', $teacherId);
                                    });
                                }
                                return $query->pluck('name', 'id');
                            })
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(fn (Set $set) => $set('learning_objective_id', null))
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        // 2. Select Tujuan Pembelajaran (TP) Terfilter sesuai Mapel
                        Select::make('learning_objective_id')
                            ->label('Tujuan Pembelajaran (TP)')
                            ->options(function (Get $get, ?GradeFormatif $record) {
                                $subjectId = $get('subject_id') ?? $record?->learningObjective?->subject_id;

                                if (!$subjectId) {
                                    return [];
                                }

                                return LearningObjective::where('subject_id', $subjectId)
                                    ->get()
                                    ->mapWithKeys(fn ($tp) => [
                                        $tp->id => "[{$tp->code}] " . \Illuminate\Support\Str::limit($tp->description, 50)
                                    ]);
                            })
                            ->searchable()
                            ->required()
                            ->disabled(fn (Get $get, ?GradeFormatif $record) => !$get('subject_id') && !$record),

                        // 3. Select Siswa (Menggunakan Model Enrollment)
                        Select::make('enrollment_id')
                            ->label('Nama Siswa')
                            ->options(function () {
                                return Enrollment::with(['student', 'class'])
                                    ->get()
                                    ->mapWithKeys(function ($enrollment) {
                                        $studentName = $enrollment->student->name ?? 'Siswa Tanpa Nama';
                                        $className = $enrollment->class->name ?? '-';
                                        return [$enrollment->id => "{$studentName} ({$className})"];
                                    });
                            })
                            ->searchable()
                            ->required(),

                        // 4. Input Nilai & Ketuntasan
                        TextInput::make('score')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required()
                            ->label('Nilai Formatif (0-100)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('is_achieved', (float)$state >= 70)),

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

                TextColumn::make('learningObjective.code')
                    ->label('Kode TP')
                    ->badge()
                    ->color('sky'),

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
            'batch' => Pages\BatchGradeFormatif::route('/batch'), // Route baru
            'edit' => Pages\EditGradeFormatif::route('/{record}/edit'),
        ];
    }
}