<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeP5ProjectResource\Pages;
use App\Models\P5Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
// 1. Hapus 'use Filament\Resources\Resource;' karena kita akan menggunakan BaseResource

class GradeP5ProjectResource extends BaseResource // 2. Ubah dari Resource menjadi BaseResource
{
    protected static ?string $model = P5Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Penilaian P5';

    protected static ?string $navigationLabel = 'Projek P5';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Master Data Projek P5')
                    ->schema([
                        // 3. Tambahkan field teacher_id agar otomatis merekam guru yang login saat pembuatan
                        Forms\Components\Select::make('teacher_id')
                            ->label('Guru Pengampu')
                            ->relationship('teacher', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->user()->teacher?->id ?? auth()->user()->teacher_id)
                            ->required(),

                        Forms\Components\Select::make('academic_year_id')
                            ->relationship('academicYear', 'year') // Menggunakan 'year'
                            ->label('Tahun Ajaran')
                            ->required(),

                        Forms\Components\Select::make('phase')
                            ->label('Fase')
                            ->options([
                                'A' => 'Fase A (Kelas 1-2)',
                                'B' => 'Fase B (Kelas 3-4)',
                                'C' => 'Fase C (Kelas 5-6)',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('theme')
                            ->label('Tema Projek')
                            ->placeholder('Contoh: Gaya Hidup Berkelanjutan')
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul Projek')
                            ->placeholder('Contoh: Pengolahan Sampah Organik')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Projek')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 4. (Opsional) Tambahkan kolom guru di tabel, disembunyikan secara default
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('academicYear.year') // Menggunakan 'year'
                    ->label('Tahun Ajaran')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phase')
                    ->label('Fase')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('theme')
                    ->label('Tema')
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Projek')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->relationship('academicYear', 'year') // Menggunakan 'year'
                    ->label('Tahun Ajaran'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Keamanan tombol delete sudah diurus otomatis oleh BaseResource
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
            'index' => Pages\ListGradeP5Projects::route('/'),
            'create' => Pages\CreateGradeP5Project::route('/create'),
            'edit' => Pages\EditGradeP5Project::route('/{record}/edit'),
        ];
    }
}