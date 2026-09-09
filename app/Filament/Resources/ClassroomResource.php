<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassroomResource\Pages;
use App\Models\ClassRoom;
use App\Traits\HasRoleScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClassroomResource extends Resource
{
    use HasRoleScope;

    protected static ?string $model = ClassRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationGroup = 'Akademik SD';

    protected static ?string $navigationLabel = 'Data Kelas / Rombel';

    protected static ?string $modelLabel = 'Kelas';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kelas')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Kelas')
                            ->required()
                            ->placeholder('Contoh: Kelas 1-A'),

                        Forms\Components\Select::make('level')
                            ->label('Tingkat Kelas')
                            ->options([
                                1 => 'Kelas 1', 2 => 'Kelas 2', 3 => 'Kelas 3',
                                4 => 'Kelas 4', 5 => 'Kelas 5', 6 => 'Kelas 6',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (in_array($state, [1, 2])) $set('phase', 'A');
                                elseif (in_array($state, [3, 4])) $set('phase', 'B');
                                elseif (in_array($state, [5, 6])) $set('phase', 'C');
                            }),

                        Forms\Components\Select::make('phase')
                            ->label('Fase Kurikulum Merdeka')
                            ->options([
                                'A' => 'Fase A (Kelas 1-2)',
                                'B' => 'Fase B (Kelas 3-4)',
                                'C' => 'Fase C (Kelas 5-6)',
                            ])
                            ->required(),

                        // FIX: Menggunakan kolom 'year' sesuai skema tabel academic_years
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->relationship('academicYear', 'year')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('teacher_id')
                            ->label('Wali Kelas')
                            ->relationship('homeroomTeacher', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user?->name ?? 'Tanpa Nama')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(['default' => 1, 'md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kelas')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('level')
                    ->label('Tingkat')
                    ->sortable(),

                Tables\Columns\TextColumn::make('phase')
                    ->label('Fase')
                    ->badge()
                    ->color('info'),

                // FIX: Memanggil 'academicYear.year' bukan 'academicYear.name'
                Tables\Columns\TextColumn::make('academicYear.year')
                    ->label('Tahun Ajaran')
                    ->sortable()
                    ->searchable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('homeroomTeacher.user.name')
                    ->label('Wali Kelas')
                    ->default('-')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('homeroomTeacher.user', function (Builder $q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->color('warning'),
                Tables\Actions\DeleteAction::make()->color('danger'),
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
            'index' => Pages\ListClassrooms::route('/'),
            'create' => Pages\CreateClassroom::route('/create'),
            'edit' => Pages\EditClassroom::route('/{record}/edit'),
        ];
    }
}