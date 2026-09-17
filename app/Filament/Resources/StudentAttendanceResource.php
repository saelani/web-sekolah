<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentAttendanceResource\Pages;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\StudentAttendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StudentAttendanceResource extends Resource
{
    protected static ?string $model = StudentAttendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Kehadiran & Akademik';

    protected static ?string $navigationLabel = 'Presensi Harian Siswa';

    protected static ?string $modelLabel = 'Presensi Siswa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Presensi Siswa')
                    ->schema([
                        // 1. Pilih Kelas Terlebih Dahulu
                        Forms\Components\Select::make('class_id')
                            ->label('Kelas')
                            ->options(function () {
                                $user = Auth::user();
                                $query = ClassRoom::query();
                                $userRole = $user->role ?? $user->role_type ?? '';

                                if (!in_array($userRole, ['admin', 'headmaster', 'super_admin']) && !($user->is_admin ?? false)) {
                                    $teacherId = $user->teacher?->id ?? $user->teacher_id;
                                    if ($teacherId) {
                                        $query->where('teacher_id', $teacherId);
                                    } else {
                                        return [];
                                    }
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live() // Membuat form reaktif terhadap perubahan kelas
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('student_id', null)) // Reset pilihan siswa jika kelas diubah
                            ->required(),

                        // 2. Pilih Siswa (Difilter berdasarkan class_id yang dipilih)
                        Forms\Components\Select::make('student_id')
                            ->label('Siswa')
                            ->options(fn (Forms\Get $get) => 
                                Student::query()
                                    ->when($get('class_id'), fn ($q, $classId) => $q->where('class_id', $classId))
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Presensi')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status Kehadiran')
                            ->options([
                                'Hadir' => 'Hadir',
                                'Sakit' => 'Sakit',
                                'Izin'  => 'Izin',
                                'Alpa'  => 'Alpa (Tanpa Keterangan)',
                            ])
                            ->default('Hadir')
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Opsional')
                            ->placeholder('Misal: Surat Dokter Ada')
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

                Tables\Columns\TextColumn::make('class.name')
                    ->label('Kelas')
                    ->sortable(),

                Tables\Columns\TextColumn::make('student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Hadir', 'H' => 'success',
                        'Sakit', 'S' => 'warning',
                        'Izin', 'I'  => 'info',
                        'Alpa', 'A'  => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'H' => 'Hadir',
                        'S' => 'Sakit',
                        'I' => 'Izin',
                        'A' => 'Alpa',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Filter Kelas')
                    ->options(ClassRoom::pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'Hadir' => 'Hadir',
                        'Sakit' => 'Sakit',
                        'Izin'  => 'Izin',
                        'Alpa'  => 'Alpa',
                        'H'     => 'Hadir (H)',
                        'S'     => 'Sakit (S)',
                        'I'     => 'Izin (I)',
                        'A'     => 'Alpa (A)',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            return $query->whereHas('class', fn ($q) => $q->where('teacher_id', $teacherId));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getPages(): array
    {
        return [
            'index'            => Pages\ListStudentAttendances::route('/'),
            'create'           => Pages\CreateStudentAttendance::route('/create'),
            'batch-attendance' => Pages\BatchStudentAttendance::route('/batch'),
            'edit'             => Pages\EditStudentAttendance::route('/{record}/edit'),
        ];
    }
}