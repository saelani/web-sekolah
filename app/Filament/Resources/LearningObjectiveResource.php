<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningObjectiveResource\Pages;
use App\Models\LearningObjective;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LearningObjectiveResource extends Resource
{
    protected static ?string $model = LearningObjective::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Akademik SD';
    protected static ?string $navigationLabel = 'Tujuan Pembelajaran (TP)';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pemetaan Tujuan Pembelajaran')
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

                        Forms\Components\TextInput::make('level')
                            ->label('Tingkat Kelas (1-6)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(6)
                            ->required(),

                        Forms\Components\Select::make('semester')
                            ->label('Semester')
                            ->options(['1' => 'Semester 1', '2' => 'Semester 2'])
                            ->required(),

                        Forms\Components\TextInput::make('chapter_number')
                            ->label('Bab / Lingkup Materi')
                            ->placeholder('Contoh: Bab 1')
                            ->maxLength(20),

                        Forms\Components\TextInput::make('chapter_name')
                            ->label('Nama Bab / Tema')
                            ->placeholder('Contoh: Pancasila Dalam Kehidupan')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('code')
                            ->label('Kode TP')
                            ->placeholder('Contoh: TP-1.1')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi TP')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Mapel')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('chapter_number')
                    ->label('Bab')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('chapter_name')
                    ->label('Nama Bab')
                    ->wrap()
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phase')
                    ->label('Fase')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('level')
                    ->label('Kelas')
                    ->sortable(),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Sem.'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi TP')
                    ->wrap()
                    ->limit(60),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->color('warning'),
                Tables\Actions\DeleteAction::make()->color('danger'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLearningObjectives::route('/'),
            'create' => Pages\CreateLearningObjective::route('/create'),
            'edit'   => Pages\EditLearningObjective::route('/{record}/edit'),
        ];
    }
}