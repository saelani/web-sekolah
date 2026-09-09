<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Models\Teacher;
use App\Models\User;
use App\Traits\HasRoleScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TeacherResource extends Resource
{
    use HasRoleScope;

    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Data Guru';

    protected static ?string $modelLabel = 'Guru';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Akun Pengguna & Nama')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Akun Pengguna (User)')
                            ->options(function () {
                                return User::query()
                                    ->withoutGlobalScopes()
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $user = User::withoutGlobalScopes()->find($state);
                                    if ($user) {
                                        $set('name', $user->name);
                                    }
                                }
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih akun user untuk guru ini'),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap Guru')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama lengkap tanpa gelar'),

                        Forms\Components\TextInput::make('front_title')
                            ->label('Gelar Depan')
                            ->placeholder('Contoh: Dr., Drs.'),

                        Forms\Components\TextInput::make('back_title')
                            ->label('Gelar Belakang')
                            ->placeholder('Contoh: S.Pd., M.Pd.'),
                    ])->columns(2),

                Forms\Components\Section::make('Identitas & Status Kepegawaian')
                    ->schema([
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->maxLength(18)
                            ->placeholder('18 digit NIP'),

                        Forms\Components\TextInput::make('nuptk')
                            ->label('NUPTK')
                            ->maxLength(16)
                            ->placeholder('16 digit NUPTK'),

                        // DIPASTIKAN SESUAI DENGAN ENUM ('L', 'P') DI DATABASE
                        Forms\Components\Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('role_type')
                            ->label('Tipe Peran / Jabatan')
                            ->options([
                                'headmaster' => 'Kepala Sekolah',
                                'class_teacher' => 'Wali Kelas',
                                'subject_teacher' => 'Guru Mata Pelajaran',
                            ])
                            ->native(false),

                        Forms\Components\FileUpload::make('photo_path')
                            ->label('Foto Profil')
                            ->image()
                            ->disk('public') // Menyimpan file ke storage/app/public/teachers-photos
                            ->directory('teachers-photos')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public') // Memastikan membaca dari folder storage/app/public
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'User') . '&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->formatStateUsing(fn ($record) => trim("{$record->front_title} {$record->name} {$record->back_title}"))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->default('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('gender')
                    ->label('JK')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ((string) $state) {
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                        default => '-',
                    })
                    ->color(fn ($state) => match ((string) $state) {
                        'L' => 'info',
                        'P' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('role_type')
                    ->label('Jabatan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ((string) $state) {
                        'headmaster' => 'Kepala Sekolah',
                        'class_teacher' => 'Wali Kelas',
                        'subject_teacher' => 'Guru Mapel',
                        default => '-',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
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


    // ... di dalam class TeacherResource ...

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        // admin','headmaster','teacher'
        // Jika Super Admin, Admin, atau Headmaster -> tampilkan semua data guru
        if ($user->hasRole(['admin', 'headmaster', 'teacher'])) {
            return $query;
        }

        // Jika guru biasa -> hanya bisa melihat data profilnya sendiri
        return $query->where('user_id', $user->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}