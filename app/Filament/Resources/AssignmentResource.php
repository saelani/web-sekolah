<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentResource\Pages;
use App\Models\Assignment;
use App\Models\Subject;
use App\Models\TeacherSubjectClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignmentResource extends BaseResource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Akademik SD';

    protected static ?string $navigationLabel = 'Tugas Kelas';

    protected static ?string $modelLabel = 'Tugas';

    protected static ?int $navigationSort = 5;

    /**
     * Override Query Scope agar Guru hanya melihat tugas dari mapel & kelas yang diampu
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
                         ->whereIn('class_id', $classRoomIds);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tugas')
                    ->schema([
                        Forms\Components\Select::make('class_id')
                            ->label('Kelas Tujuan')
                            ->relationship('classRoom', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

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
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul Tugas')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->label('Petunjuk / Deskripsi Tugas')
                            ->nullable()
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('due_date')
                            ->label('Tenggat Waktu (Deadline)')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('classRoom.name')
                    ->label('Kelas')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Tugas')
                    ->searchable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Tenggat Waktu')
                    ->dateTime('d M Y, H:i')
                    ->color('danger')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('classRoom', 'name'),

                // --- FILTER MATA PELAJARAN DISESUAIKAN BERDASARKAN GURU ---
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
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }
}