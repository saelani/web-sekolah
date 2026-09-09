<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeExtracurricularResource\Pages;
use App\Models\ExtracurricularScore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GradeExtracurricularResource extends Resource
{
    // Menggunakan Model ExtracurricularScore sesuai tabel grade_extracurricular_scores
    protected static ?string $model = ExtracurricularScore::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'Penilaian';
    protected static ?string $navigationLabel = 'Nilai Ekstrakurikuler';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Penilaian Ekstrakurikuler')
                    ->schema([
                        Forms\Components\Select::make('enrollment_id')
                            ->relationship('enrollment.student', 'name')
                            ->label('Siswa / Pendaftaran')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('extracurricular_id')
                            ->relationship('extracurricular', 'name')
                            ->label('Kegiatan Ekstrakurikuler')
                            ->required(),

                        Forms\Components\TextInput::make('grade')
                            ->label('Nilai / Predikat')
                            ->placeholder('Contoh: A / Sangat Baik / 85')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi / Catatan Capaian')
                            ->rows(3)
                            ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('enrollment.classRoom.name')
                    ->label('Kelas')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('extracurricular.name')
                    ->label('Ekstrakurikuler')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('grade')
                    ->label('Nilai / Predikat')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('extracurricular_id')
                    ->relationship('extracurricular', 'name')
                    ->label('Ekstrakurikuler'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGradeExtracurriculars::route('/'),
            'create' => Pages\CreateGradeExtracurricular::route('/create'),
            'edit'   => Pages\EditGradeExtracurricular::route('/{record}/edit'),
        ];
    }
}