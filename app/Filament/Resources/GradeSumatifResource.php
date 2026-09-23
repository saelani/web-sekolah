<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeSumatifResource\Pages;
use App\Models\ClassRoom;
use App\Models\Enrollment;
use App\Models\GradeSumatif;
use App\Models\SumativeScope;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
// 1. Hapus 'use Filament\Resources\Resource;'

class GradeSumatifResource extends BaseResource // 2. Ubah extends dari Resource menjadi BaseResource
{
    protected static ?string $model = GradeSumatif::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Penilaian';

    protected static ?string $navigationLabel = 'Nilai Sumatif';

    protected static ?int $navigationSort = 2;

    // 3. Method getEloquentQuery() TETAP DIPERTAHANKAN
    // Alasan: Untuk Eager Loading (optimasi query). 
    // parent::getEloquentQuery() akan tetap mengeksekusi filter otomatis dari BaseResource!
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'enrollment.student',
            'enrollment.class',
            'subject',
            'sumativeScope',
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Penilaian Sumatif Siswa')
                    ->schema([
                        // 4. Tambahkan field teacher_id agar tersimpan siapa guru yang menginput nilai ini
                        // (Pastikan tabel grade_sumatifs di database Anda sudah memiliki kolom teacher_id)
                        Forms\Components\Select::make('teacher_id')
                            ->label('Guru Penilai')
                            ->relationship('teacher', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->user()->teacher?->id ?? auth()->user()->teacher_id)
                            ->required(),

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

                        Forms\Components\Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->label('Mata Pelajaran')
                            ->searchable()
                            ->preload()
                            ->required(),

                        // Menggunakan SumativeScope sebagai pilihan Lingkup Materi / Bab
                        Forms\Components\Select::make('sumative_scope_id')
                            ->relationship('sumativeScope', 'name')
                            ->label('Lingkup Materi / Bab')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('type')
                            ->label('Jenis Sumatif')
                            ->options([
                                'TP' => 'Tujuan Pembelajaran (TP)',
                                'STS' => 'Sumatif Tengah Semester (STS)',
                                'SAS' => 'Sumatif Akhir Semester (SAS)',
                                'SAT' => 'Sumatif Akhir Tahun (SAT)',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('score')
                            ->label('Nilai')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->placeholder('0 - 100')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 5. Tambahkan kolom untuk menampilkan nama guru penilai (Disembunyikan secara default)
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru Penilai')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('enrollment.student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrollment.class.name')
                    ->label('Kelas')
                    ->badge(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),

                // Menampilkan kolom Lingkup Materi / Bab dari model SumativeScope
                Tables\Columns\TextColumn::make('sumativeScope.name')
                    ->label('Lingkup Materi / Bab')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('score')
                    ->label('Nilai')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                // Filter Berdasarkan Mata Pelajaran
                Tables\Filters\SelectFilter::make('subject_id')
                    ->relationship('subject', 'name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->preload(),

                // Filter Berdasarkan Lingkup Materi / Bab
                Tables\Filters\SelectFilter::make('sumative_scope_id')
                    ->relationship('sumativeScope', 'name')
                    ->label('Lingkup Materi / Bab')
                    ->searchable()
                    ->preload(),

                // Filter Berdasarkan Kelas
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->options(ClassRoom::pluck('name', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('enrollment', function ($q) use ($data) {
                                $q->where('class_id', $data['value']);
                            });
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Keamanan tombol delete dari BaseResource
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
            'index' => Pages\ListGradeSumatifs::route('/'),
            'create' => Pages\CreateGradeSumatif::route('/create'),
            'edit' => Pages\EditGradeSumatif::route('/{record}/edit'),
        ];
    }
}