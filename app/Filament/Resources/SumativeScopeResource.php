<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SumativeScopeResource\Pages;
use App\Models\SumativeScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SumativeScopeResource extends Resource
{
    protected static ?string $model = SumativeScope::class;

    protected static ?string $navigationIcon = 'heroicon-o-bookmark-square';
    protected static ?string $navigationGroup = 'Kurikulum & Akademik';
    protected static ?string $navigationLabel = 'Lingkup Materi (Bab)';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Lingkup Materi / Bab')
                    ->schema([
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
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
            'index'  => Pages\ListSumativeScopes::route('/'),
            'create' => Pages\CreateSumativeScope::route('/create'),
            'edit'   => Pages\EditSumativeScope::route('/{record}/edit'),
        ];
    }
}