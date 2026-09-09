<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeSumatifResource\Pages;
use App\Models\GradeSumatif;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GradeSumatifResource extends Resource
{
    protected static ?string $model = GradeSumatif::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Penilaian';
    protected static ?string $navigationLabel = 'Nilai Sumatif';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Penilaian Sumatif Siswa')
                    ->schema([
                        Forms\Components\Select::make('enrollment_id')
                            ->relationship('enrollment.student', 'name')
                            ->label('Siswa / Pendaftaran')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->label('Mata Pelajaran')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('sumative_scope_id')
                            ->relationship('sumativeScope', 'name') // Nama fungsi relasi camelCase: sumativeScope
                            ->label('Lingkup Materi / Sumatif Scope')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('Jenis Sumatif')
                            ->options([
                                'TP'   => 'Tujuan Pembelajaran (TP)',
                                'STS'  => 'Sumatif Tengah Semester (STS)',
                                'SAS'  => 'Sumatif Akhir Semester (SAS)',
                                'SAT'  => 'Sumatif Akhir Tahun (SAT)',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('score')
                            ->label('Nilai')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->placeholder('0 - 100')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('enrollment.student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                // Menggunakan relasi enrollment.class.name (mengacu ke class_id di model Enrollment)
                Tables\Columns\TextColumn::make('enrollment.class.name')
                    ->label('Kelas')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sumativeScope.name')
                    ->label('Lingkup Materi')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('score')
                    ->label('Nilai')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->relationship('subject', 'name')
                    ->label('Mata Pelajaran'),

                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('enrollment.class', 'name'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGradeSumatifs::route('/'),
            'create' => Pages\CreateGradeSumatif::route('/create'),
            'edit'   => Pages\EditGradeSumatif::route('/{record}/edit'),
        ];
    }
}