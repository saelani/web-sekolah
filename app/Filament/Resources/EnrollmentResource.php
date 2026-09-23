<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use App\Traits\HasRoleScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
// 1. Hapus 'use Filament\Resources\Resource;'

class EnrollmentResource extends BaseResource // 2. Ubah dari Resource menjadi BaseResource
{
    use HasRoleScope;

    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $navigationLabel = 'Rombel / Siswa Kelas';

    protected static ?int $navigationSort = 3;

    // 3. Tambahkan getEloquentQuery di sini untuk memfilter data tabel berdasarkan Wali Kelas
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Jika bukan guru (misal Admin / Kepala Sekolah), kembalikan query utuh
        if (! $user || $user->role !== 'teacher') {
            return $query;
        }

        $teacherId = $user->teacher?->id ?? $user->teacher_id ?? $user->id;

        // Jika Guru, tampilkan siswa yang ada di kelas miliknya saja (Wali Kelas)
        return $query->whereHas('class', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Penempatan Siswa (Rombel)')
                    ->schema([
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->relationship('academicYear', 'year')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('class_id')
                            ->label('Kelas / Rombel')
                            ->relationship(
                                name: 'class',
                                titleAttribute: 'name',
                                modifyQueryUsing: function ($query) {
                                    $user = auth()->user();
                                    
                                    // 4. Perbaikan logika filter pada Dropdown Kelas
                                    if ($user && $user->role === 'teacher') {
                                        $teacherId = $user->teacher?->id ?? $user->teacher_id ?? $user->id;
                                        return $query->where('teacher_id', $teacherId);
                                    }

                                    return $query;
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('student_id')
                            ->label('Siswa')
                            ->relationship('student', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('academicYear.year')
                    ->label('Tahun Ajaran')
                    ->sortable()
                    ->searchable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('class.name')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('student.nisn')
                    ->label('NISN')
                    ->searchable()
                    ->default('-'),

                Tables\Columns\TextColumn::make('student.name')
                    ->label('Nama Siswa')
                    ->weight('bold')
                    ->searchable()
                    ->sortable()
                    ->default('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Filter Kelas')
                    ->relationship('class', 'name'),

                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Filter Tahun Ajaran')
                    ->relationship('academicYear', 'year'),
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

    // 5. Method getTableQuery() yang salah tempat SUDAH DIHAPUS.

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}