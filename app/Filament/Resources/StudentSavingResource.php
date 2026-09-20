<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentSavingResource\Pages;
use App\Models\ClassRoom;
use App\Models\StudentSaving;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StudentSavingResource extends Resource
{
    protected static ?string $model = StudentSaving::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Keuangan & Ops';

    protected static ?string $navigationLabel = 'Tabungan Siswa';

    protected static ?string $modelLabel = 'Tabungan Siswa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Transaksi Tabungan')
                    ->schema([
                        Forms\Components\Select::make('student_id')
                            ->label('Siswa')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => Auth::id()),

                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Transaksi')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('Jenis Transaksi')
                            ->options([
                                'in' => 'Setor (Masuk)',
                                'out' => 'Tarik (Keluar)',
                            ])
                            ->default('in')
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Nominal (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('student.classRoom.name')
                    ->label('Kelas')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Jenis')
                    ->colors([
                        'success' => 'in',
                        'danger' => 'out',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Setor',
                        'out' => 'Tarik',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('recorder.name')
                    ->label('Petugas')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Filter Kelas')
                    ->options(ClassRoom::pluck('name', 'id'))
                    ->query(fn (Builder $query, array $data) => $data['value'] ? $query->whereHas('student', fn ($q) => $q->where('class_id', $data['value'])) : $query),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Transaksi')
                    ->options([
                        'in' => 'Setor',
                        'out' => 'Tarik',
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        $userRole = $user->role ?? $user->role_type ?? '';

        if (in_array($userRole, ['admin', 'headmaster', 'super_admin']) || ($user->is_admin ?? false)) {
            return $query;
        }

        $teacherId = $user->teacher?->id ?? $user->teacher_id;
        if ($teacherId) {
            return $query->whereHas('student.classRoom', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentSavings::route('/'),
            'create' => Pages\CreateStudentSaving::route('/create'),
            'batch-saving' => Pages\BatchStudentSaving::route('/batch'),
            'edit' => Pages\EditStudentSaving::route('/{record}/edit'),
        ];
    }
}
