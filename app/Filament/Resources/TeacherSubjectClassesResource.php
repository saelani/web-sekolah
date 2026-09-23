<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherSubjectClassesResource\Pages;
use App\Models\TeacherSubjectClass;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Traits\HasRoleScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeacherSubjectClassesResource extends Resource
{
    use HasRoleScope;

    protected static ?string $model = TeacherSubjectClass::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $navigationLabel = 'Pembagian Pengajar (Mapel)';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Setting Pengajar Mata Pelajaran')
                    ->schema([
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->relationship('academicYear', 'year')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('teacher_id')
                            ->label('Guru Pengajar')
                            ->relationship('teacher', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('subject_id')
                            ->label('Mata Pelajaran')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        // SESUAIKAN DI SINI
                        Forms\Components\Select::make('class_id')
                            ->label('Kelas / Rombel')
                            ->relationship('classRoom', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('academicYear.year')
                    ->label('Tahun Ajaran')
                    ->sortable()
                    ->searchable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Nama Guru')
                    ->weight('bold')
                    ->sortable()
                    ->searchable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable()
                    ->default('-'),

                // SESUAIKAN DI SINI
                Tables\Columns\TextColumn::make('classRoom.name')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable()
                    ->default('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Filter Tahun Ajaran')
                    ->relationship('academicYear', 'year'),

                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Filter Mata Pelajaran')
                    ->relationship('subject', 'name'),

                // SESUAIKAN DI SINI
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Filter Kelas')
                    ->relationship('classRoom', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->color('warning'),
                Tables\Actions\DeleteAction::make()->color('danger'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeacherSubjectClass::route('/'),
            'create' => Pages\CreateTeacherSubjectClass::route('/create'),
            'edit' => Pages\EditTeacherSubjectClass::route('/{record}/edit'),
        ];
    }
}