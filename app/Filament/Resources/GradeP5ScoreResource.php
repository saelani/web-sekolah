<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeP5ScoreResource\Pages;
use App\Models\GradeP5Score;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GradeP5ScoreResource extends Resource
{
    protected static ?string $model = GradeP5Score::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'Penilaian P5';
    protected static ?string $navigationLabel = 'Input Nilai P5';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Penilaian P5 Siswa')
                    ->schema([
                        Forms\Components\Select::make('enrollment_id')
                            ->relationship('enrollment.student', 'name')
                            ->label('Siswa / Pendaftaran')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('p5_subelement_id')
                            ->relationship('subelement', 'subelement_name')
                            ->label('Sub-elemen P5')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('score')
                            ->label('Capaian P5')
                            ->options([
                                'BB'  => 'Belum Berkembang (BB)',
                                'MB'  => 'Mulai Berkembang (MB)',
                                'BSH' => 'Berkembang Sesuai Harapan (BSH)',
                                'SB'  => 'Sangat Berkembang (SB)',
                            ])
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

                // Menggunakan relasi enrollment.class.name (sesuai method class() di Model Enrollment)
                Tables\Columns\TextColumn::make('enrollment.class.name')
                    ->label('Kelas')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subelement.subelement_name')
                    ->label('Sub-elemen P5')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('score')
                    ->label('Predikat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SB'  => 'success',
                        'BSH' => 'info',
                        'MB'  => 'warning',
                        'BB'  => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('p5_subelement_id')
                    ->relationship('subelement', 'subelement_name')
                    ->label('Sub-elemen'),

                // Filter berdasarkan Kelas menggunakan relasi bertingkat ke Enrollment
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('enrollment.class', 'name'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGradeP5Scores::route('/'),
            'create' => Pages\CreateGradeP5Score::route('/create'),
            'batch'  => Pages\BatchGradeP5Score::route('/batch'),
            'edit'   => Pages\EditGradeP5Score::route('/{record}/edit'),
        ];
    }
}