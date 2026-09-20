<?php

namespace App\Filament\Resources;

use App\Exports\StudentsExport;
use App\Filament\Resources\StudentResource\Pages;
use App\Imports\StudentsImport;
use App\Models\Student;
use App\Traits\HasRoleScope;
use App\Traits\HasUniversalExportImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentResource extends Resource
{
    use HasRoleScope;
    use HasUniversalExportImport;

    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Akademik SD';

    protected static ?string $navigationLabel = 'Data Siswa';

    protected static ?string $modelLabel = 'Siswa';

    protected static ?int $navigationSort = 2;

    /**
     * Override Query Scope agar Guru bisa melihat siswa di kelasnya
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (! $user) {
            return $query;
        }

        // 1. Jika User adalah GURU
        if ($user->hasRole('teacher')) {
            $teacher = $user->teacher;

            if (! $teacher) {
                return $query->whereRaw('1 = 0'); // Jika profil teacher tidak ada, sembunyikan semua
            }

            // Ambil ID kelas wali kelas & ID kelas mapel yang diajar
            $homeroomClassIds = $teacher->homeroomClasses()->pluck('id')->toArray();
            $subjectClassIds = $teacher->teacherSubjectClasses()->pluck('class_id')->toArray();

            $allClassIds = array_unique(array_merge($homeroomClassIds, $subjectClassIds));

            return $query->whereIn('class_id', $allClassIds);
        }

        // 2. Jika User adalah SISWA (hanya lihat diri sendiri)
        if ($user->hasRole('student')) {
            return $query->where('user_id', $user->id);
        }

        // 3. Admin & Headmaster (melihat seluruh siswa)
        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // AKUN LOGIN TERPUSAT (Tabel Users)
                Forms\Components\Section::make('Akun Login Siswa')
                    ->description('Kredensial akun terpusat untuk login portal siswa.')
                    ->relationship('user')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Akun')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Email / ID Login')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                    ])->columns(['default' => 1, 'md' => 3]),

                Forms\Components\Section::make('Informasi Akademik & Kelas')
                    ->schema([
                        Forms\Components\Select::make('class_id')
                            ->label('Rombongan Belajar / Kelas')
                            ->relationship('classRoom', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Forms\Components\TextInput::make('nisn')
                            ->label('NISN')
                            ->required()
                            ->numeric()
                            ->length(10)
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('nis')
                            ->label('NIS Lokal')
                            ->numeric()
                            ->maxLength(20),
                    ])->columns(['default' => 1, 'md' => 3]),

                Forms\Components\Section::make('Biodata Diri Siswa')
                    ->schema([
                        Forms\Components\FileUpload::make('photo_path')
                            ->label('Foto Siswa')
                            ->image()
                            ->avatar()
                            ->directory('students')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap Siswa')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('religion')
                            ->label('Agama')
                            ->options([
                                'Islam' => 'Islam',
                                'Kristen' => 'Kristen',
                                'Katolik' => 'Katolik',
                                'Hindu' => 'Hindu',
                                'Buddha' => 'Buddha',
                                'Khonghucu' => 'Khonghucu',
                            ])
                            ->default('Islam')
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('pob')
                            ->label('Tempat Lahir')
                            ->maxLength(100),

                        Forms\Components\DatePicker::make('dob')
                            ->label('Tanggal Lahir')
                            ->native(false),

                        Forms\Components\TextInput::make('parent_name')
                            ->label('Nama Orang Tua / Wali')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('address')
                            ->label('Alamat Domisili')
                            ->columnSpanFull(),
                    ])->columns(['default' => 1, 'md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->circular(),

                Tables\Columns\TextColumn::make('nisn')
                    ->label('NISN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('info'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email Login')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('classRoom.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gender')
                    ->label('JK')
                    ->formatStateUsing(fn (string $state): string => $state === 'L' ? 'Laki-Laki' : 'Perempuan'),

                Tables\Columns\TextColumn::make('religion')
                    ->label('Agama')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('parent_name')
                    ->label('Orang Tua/Wali')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Filter Kelas')
                    ->relationship('classRoom', 'name'),
            ])
            ->headerActions([
                static::getImportAction(StudentsImport::class),
                static::getExportAction(StudentsExport::class, 'data-siswa-'.date('Y-m-d').'.xlsx'),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
