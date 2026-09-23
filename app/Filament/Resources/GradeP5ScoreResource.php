<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeP5ScoreResource\Pages;
use App\Models\GradeP5Score;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
// 1. Hapus 'use Filament\Resources\Resource;'

class GradeP5ScoreResource extends BaseResource // 2. Ubah extends dari Resource menjadi BaseResource
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
                        // 3. Tambahkan field teacher_id agar tersimpan siapa guru yang menginput nilai ini
                        // (Pastikan tabel grade_p5_scores di database Anda sudah memiliki kolom teacher_id)
                        Forms\Components\Select::make('teacher_id')
                            ->label('Guru Penilai')
                            ->relationship('teacher', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->user()->teacher?->id ?? auth()->user()->teacher_id)
                            ->required(),

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
                                'BB' => 'Belum Berkembang (BB)',
                                'MB' => 'Mulai Berkembang (MB)',
                                'BSH' => 'Berkembang Sesuai Harapan (BSH)',
                                'SB' => 'Sangat Berkembang (SB)',
                            ])
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 4. Tambahkan kolom untuk menampilkan nama guru penilai (Disembunyikan secara default)
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru Penilai')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
                        'SB' => 'success',
                        'BSH' => 'info',
                        'MB' => 'warning',
                        'BB' => 'danger',
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
            'index' => Pages\ListGradeP5Scores::route('/'),
            'create' => Pages\CreateGradeP5Score::route('/create'),
            'batch' => Pages\BatchGradeP5Score::route('/batch'), // Halaman custom Batch Insert tetap aman
            'edit' => Pages\EditGradeP5Score::route('/{record}/edit'),
        ];
    }
}