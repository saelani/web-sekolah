<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExtracurricularResource\Pages;
use App\Models\Extracurricular;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
// 1. Hapus 'use Filament\Resources\Resource;'

class ExtracurricularResource extends BaseResource // 2. Ubah extends dari Resource menjadi BaseResource
{
    protected static ?string $model = Extracurricular::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Penilaian';

    protected static ?string $navigationLabel = 'Master Ekstrakurikuler';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Ekstrakurikuler')
                    ->schema([
                        // 3. Tambahkan field teacher_id untuk melacak pembuat/guru yang mengelola
                        // (Pastikan tabel extracurriculars di database sudah memiliki kolom teacher_id)
                        Forms\Components\Select::make('teacher_id')
                            ->label('Guru Penanggung Jawab / Pembina Utama')
                            ->relationship('teacher', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->user()->teacher?->id ?? auth()->user()->teacher_id)
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Ekstrakurikuler')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('instructor_name')
                            ->label('Nama Pelatih Luar (Opsional)')
                            ->helperText('Isi jika pelatih bukan dari kalangan guru.')
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 4. Tambahkan kolom untuk menampilkan nama guru penanggung jawab (Disembunyikan secara default)
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru Penanggung Jawab')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Ekstrakurikuler')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('instructor_name')
                    ->label('Pelatih Luar')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('scores_count')
                    ->counts('scores')
                    ->label('Jumlah Siswa')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListExtracurriculars::route('/'),
            'create' => Pages\CreateExtracurricular::route('/create'),
            'edit' => Pages\EditExtracurricular::route('/{record}/edit'),
        ];
    }
}