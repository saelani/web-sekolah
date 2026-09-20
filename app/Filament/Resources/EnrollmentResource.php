<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use App\Traits\HasRoleScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentResource extends Resource
{
    use HasRoleScope;

    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $navigationLabel = 'Rombel / Siswa Kelas';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Penempatan Siswa (Rombel)')
                    ->schema([
                        Forms\Components\Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->relationship('academicYear', 'year') // Sesuaikan kolom ke 'year'
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
                                    $userRole = $user->role ?? $user->role_type ?? '';

                                    // Jika yang login Guru/Wali Kelas, kunci hanya pada kelas yang diajar
                                    if (in_array($userRole, ['teacher', 'class_teacher', 'subject_teacher']) && $user->teacher) {
                                        return $query->where('teacher_id', $user->teacher->id);
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
            // ...

            ->filters([
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Filter Kelas')
                    ->relationship('class', 'name'),

                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Filter Tahun Ajaran')
                    ->relationship('academicYear', 'year'), // Sesuaikan kolom ke 'year'
            ])
            ->actions([
                Tables\Actions\EditAction::make()->color('warning'),
                Tables\Actions\DeleteAction::make()->color('danger'),
            ]);
    }

    // Tambahkan method ini di dalam class ListEnrollments
    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        $user = auth()->user();
        $userRole = $user->role ?? $user->role_type ?? '';

        // Jika yang login Guru/Wali Kelas, tampilkan siswa yang ada di kelas miliknya saja
        if (in_array($userRole, ['teacher', 'class_teacher', 'subject_teacher']) && $user->teacher) {
            return $query->whereHas('class', function ($q) use ($user) {
                $q->where('teacher_id', $user->teacher->id);
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}
