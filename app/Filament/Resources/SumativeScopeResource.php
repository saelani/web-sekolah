<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SumativeScopeResource\Pages;
use App\Models\SumativeScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
// 1. Hapus 'use Filament\Resources\Resource;' 
// Karena BaseResource sudah berada di namespace yang sama (App\Filament\Resources), kita bisa langsung meng-extend-nya.

class SumativeScopeResource extends BaseResource // 2. UBAH extends Resource menjadi BaseResource
{
    protected static ?string $model = SumativeScope::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';

    protected static ?string $navigationGroup = 'Kurikulum & Akademik';

    protected static ?string $navigationLabel = 'Lingkup Materi (Bab)';

    protected static ?int $navigationSort = 3;

    // 3. METHOD getEloquentQuery() TIDAK PERLU DITULIS LAGI DI SINI
    // BaseResource Anda sudah otomatis mengecek:
    // - Apakah tabel ini punya kolom 'teacher_id'?
    // - Jika tidak, apakah model ini punya relasi 'subject' yang terhubung ke TeacherSubjectClass?
    // Semua filter keamanan sudah di-handle oleh BaseResource!

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Lingkup Materi / Bab')
                    ->schema([
                        // Jika di tabel database sumative_scopes Anda menambahkan kolom 'teacher_id', 
                        // biarkan field ini. Jika tidak, BaseResource tetap akan mem-filter berdasarkan relasi 'subject_id'
                        Forms\Components\Select::make('teacher_id')
                            ->label('Guru Pengampu')
                            ->relationship('teacher', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->user()->teacher?->id ?? auth()->user()->teacher_id)
                            ->required(),

                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->label('Mata Pelajaran')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('phase')
                            ->label('Fase')
                            ->options([
                                'A' => 'Fase A (Kelas 1-2)',
                                'B' => 'Fase B (Kelas 3-4)',
                                'C' => 'Fase C (Kelas 5-6)',
                            ])
                            ->required(),

                        Forms\Components\Select::make('semester')
                            ->label('Semester')
                            ->options([
                                1 => 'Semester 1',
                                2 => 'Semester 2',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lingkup Materi / Bab')
                            ->placeholder('Contoh: Bab 1 - Bilangan Cacat')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Sembunyikan default agar tabel tidak penuh

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Lingkup Materi / Bab')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phase')
                    ->label('Fase')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->badge()
                    ->color('info')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->relationship('subject', 'name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('phase')
                    ->label('Fase')
                    ->options([
                        'A' => 'Fase A',
                        'B' => 'Fase B',
                        'C' => 'Fase C',
                    ]),
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
            'index' => Pages\ListSumativeScopes::route('/'),
            'create' => Pages\CreateSumativeScope::route('/create'),
            'edit' => Pages\EditSumativeScope::route('/{record}/edit'),
        ];
    }
}