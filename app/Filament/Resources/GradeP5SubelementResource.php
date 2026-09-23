<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeP5SubelementResource\Pages;
use App\Models\P5Subelement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
// 1. Hapus 'use Filament\Resources\Resource;'

class GradeP5SubelementResource extends BaseResource // 2. Ubah dari Resource menjadi BaseResource
{
    protected static ?string $model = P5Subelement::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Penilaian P5';

    protected static ?string $navigationLabel = 'Sub-elemen P5';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pemetaan Sub-elemen P5')
                    ->schema([
                        // 3. Tambahkan field teacher_id agar pembuat sub-elemen terekam otomatis
                        // (Pastikan tabel p5_subelements di database Anda sudah memiliki kolom teacher_id)
                        Forms\Components\Select::make('teacher_id')
                            ->label('Guru Pembuat')
                            ->relationship('teacher', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->user()->teacher?->id ?? auth()->user()->teacher_id)
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('p5_project_id')
                            ->relationship('project', 'title')
                            ->label('Projek P5')
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('dimension')
                            ->label('Dimensi')
                            ->placeholder('Contoh: Beriman, Bertaqwa Kepada Tuhan YME')
                            ->required(),

                        Forms\Components\TextInput::make('element')
                            ->label('Elemen')
                            ->placeholder('Contoh: Akhlak Kepada Alam')
                            ->required(),

                        Forms\Components\TextInput::make('subelement_name')
                            ->label('Nama Sub-elemen')
                            ->placeholder('Contoh: Menjaga Lingkungan Alam Sekitar')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('target_narrative')
                            ->label('Target Capaian / Narasi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 4. Tambahkan kolom guru yang disembunyikan secara default
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('project.title')
                    ->label('Projek P5')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('dimension')
                    ->label('Dimensi')
                    ->searchable(),

                Tables\Columns\TextColumn::make('element')
                    ->label('Elemen')
                    ->searchable(),

                Tables\Columns\TextColumn::make('subelement_name')
                    ->label('Sub-elemen')
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('p5_project_id')
                    ->relationship('project', 'title')
                    ->label('Projek P5'),
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
            'index' => Pages\ListGradeP5Subelements::route('/'),
            'create' => Pages\CreateGradeP5Subelement::route('/create'),
            'edit' => Pages\EditGradeP5Subelement::route('/{record}/edit'),
        ];
    }
}