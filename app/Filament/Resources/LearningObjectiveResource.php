<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningObjectiveResource\Pages;
use App\Models\LearningObjective;
use App\Models\SumativeScope;
use App\Models\ClassRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                        // 1. Pilih Mata Pelajaran
                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->label('Mata Pelajaran')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('sumative_scope_id', null))
                            ->required(),

                        // 2. Pilih Bab / Lingkup Materi (Sumative Scope)
                        Forms\Components\Select::make('sumative_scope_id')
                            ->relationship(
                                name: 'sumativeScope',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query, Forms\Get $get) => 
                                    $query->when($get('subject_id'), fn ($q, $subjectId) => $q->where('subject_id', $subjectId))
                            )
                            ->label('Bab / Lingkup Materi')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    $scope = SumativeScope::find($state);
                                    if ($scope) {
                                        $set('phase', $scope->phase);
                                        $set('semester', (string) $scope->semester);
                                    }
                                }
                            })
                            ->required(),

                        // 3. Pilih Tingkat Kelas dari ClassRoom (acad_classes)
                        Forms\Components\Select::make('level')
                            ->label('Tingkat Kelas')
                            ->options(
                                ClassRoom::query()
                                    ->select('level')
                                    ->distinct()
                                    ->orderBy('level')
                                    ->pluck('level', 'level')
                                    ->map(fn ($level) => "Kelas {$level}")
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                if ($state) {
                                    if (in_array($state, [1, 2])) $set('phase', 'A');
                                    elseif (in_array($state, [3, 4])) $set('phase', 'B');
                                    elseif (in_array($state, [5, 6])) $set('phase', 'C');
                                }
                            })
                            ->required(),

                        // 4. Fase
                        Forms\Components\Select::make('phase')
                            ->label('Fase')
                            ->options([
                                'A' => 'Fase A (Kelas 1-2)',
                                'B' => 'Fase B (Kelas 3-4)',
                                'C' => 'Fase C (Kelas 5-6)',
                            ])
                            ->required(),

                        // 5. Semester
                        Forms\Components\Select::make('semester')
                            ->label('Semester')
                            ->options([
                                '1' => 'Semester 1', 
                                '2' => 'Semester 2'
                            ])
                            ->required(),

                        // 6. Deskripsi TP (Kode TP & Chapter otomatis diisi oleh model)
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Tujuan Pembelajaran')
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

                Tables\Columns\TextColumn::make('sumativeScope.name')
                    ->label('Bab / Lingkup Materi')
                    ->wrap()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Kode TP')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phase')
                    ->label('Fase')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('level')
                    ->label('Kelas')
                    ->formatStateUsing(fn ($state) => "Kelas {$state}")
                    ->sortable(),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Sem.'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi TP')
                    ->wrap()
                    ->limit(50),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject_id')
                    ->relationship('subject', 'name')
                    ->label('Mata Pelajaran'),
                
                Tables\Filters\SelectFilter::make('sumative_scope_id')
                    ->relationship('sumativeScope', 'name')
                    ->label('Bab / Lingkup Materi'),

                Tables\Filters\SelectFilter::make('level')
                    ->options(
                        ClassRoom::query()
                            ->select('level')
                            ->distinct()
                            ->orderBy('level')
                            ->pluck('level', 'level')
                            ->map(fn ($level) => "Kelas {$level}")
                    )
                    ->label('Tingkat Kelas'),
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