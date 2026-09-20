<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeExtracurricularResource\Pages;
use App\Models\Enrollment;
use App\Models\ExtracurricularScore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GradeExtracurricularResource extends Resource
{
    protected static ?string $model = ExtracurricularScore::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Penilaian';

    protected static ?string $navigationLabel = 'Nilai Ekstrakurikuler';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'enrollment.student',
            'enrollment.class',
            'extracurricular',
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Penilaian Ekstrakurikuler')
                    ->schema([
                        Forms\Components\Select::make('enrollment_id')
                            ->label('Siswa / Pendaftaran')
                            ->options(
                                Enrollment::with(['student', 'class'])->get()->mapWithKeys(function ($enrollment) {
                                    $studentName = optional($enrollment->student)->name ?? 'Siswa Tanpa Nama';
                                    $className = optional($enrollment->class)->name ?? 'Tanpa Kelas';

                                    return [$enrollment->id => "{$studentName} ({$className})"];
                                })
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('extracurricular_id')
                            ->relationship('extracurricular', 'name')
                            ->label('Kegiatan Ekstrakurikuler')
                            ->searchable()
                            ->preload()
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

                Tables\Columns\TextColumn::make('enrollment.class.name')
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
                    ->label('Ekstrakurikuler')
                    ->searchable()
                    ->preload(),
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
            'index' => Pages\ListGradeExtracurriculars::route('/'),
            'create' => Pages\CreateGradeExtracurricular::route('/create'),
            'edit' => Pages\EditGradeExtracurricular::route('/{record}/edit'),
        ];
    }
}
