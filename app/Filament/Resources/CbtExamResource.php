<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CbtExamResource\Pages;
use App\Models\CbtExam;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Imports\CbtQuestionImport;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;


class CbtExamResource extends Resource
{
    protected static ?string $model = CbtExam::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Manajemen CBT & Nilai';

    protected static ?string $navigationLabel = 'Ujian CBT';

    protected static ?string $modelLabel = 'Ujian CBT';

    protected static ?string $pluralModelLabel = 'Daftar Ujian CBT';

    /**
     * Memfilter data berdasarkan role_type user.
     * Kepala Sekolah (headmaster) & Admin dapat melihat semua ujian,
     * sedangkan Guru Kelas / Guru Mapel hanya dapat melihat ujian milik sendiri.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->role_type === 'headmaster' || $user->is_admin) {
            return $query;
        }

        $teacherId = $user->teacher?->id ?? $user->teacher_id;

        return $query->where('teacher_id', $teacherId);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SECTION 1: Informasi Utama Ujian
                Forms\Components\Section::make('Informasi Ujian')
                    ->description('Atur konfigurasi dasar pelaksanaan ujian CBT.')
                    ->schema([
                        Forms\Components\Select::make('subject_id')
                            ->label('Mata Pelajaran')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('teacher_id')
                            ->label('Guru Pengampu')
                            ->relationship('teacher', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->user()->teacher?->id ?? auth()->user()->teacher_id)
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Judul Ujian')
                            ->placeholder('Contoh: Sumatif Tengah Semester (STS) IPAS')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('duration_minutes')
                            ->label('Durasi (Menit)')
                            ->numeric()
                            ->default(60)
                            ->suffix('Menit')
                            ->required(),

                        Forms\Components\TextInput::make('token')
                            ->label('Token Ujian')
                            ->default(fn () => strtoupper(Str::random(6)))
                            ->required()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generateToken')
                                    ->icon('heroicon-m-arrow-path')
                                    ->tooltip('Acak Token Baru')
                                    ->action(fn (Forms\Set $set) => $set('token', strtoupper(Str::random(6))))
                            ),

                        Forms\Components\DateTimePicker::make('start_time')
                            ->label('Waktu Mulai')
                            ->required()
                            ->native(false),

                        Forms\Components\DateTimePicker::make('end_time')
                            ->label('Waktu Selesai')
                            ->required()
                            ->native(false)
                            ->after('start_time'),
                    ])
                    ->columns(2),

                // SECTION 2: Pengaturan Pelaksanaan
                Forms\Components\Section::make('Pengaturan & Aturan Ujian')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Jika diaktifkan, siswa dapat melihat ujian ini.')
                            ->default(true),

                        Forms\Components\Toggle::make('randomize_questions')
                            ->label('Acak Urutan Soal')
                            ->default(true),

                        Forms\Components\Toggle::make('randomize_options')
                            ->label('Acak Urutan Opsi Jawaban')
                            ->default(true),

                        Forms\Components\Toggle::make('show_result')
                            ->label('Tampilkan Hasil/Nilai')
                            ->helperText('Siswa dapat langsung melihat nilai setelah selesai submission.')
                            ->default(false),
                    ])
                    ->columns(2),

                // SECTION 3: Input Soal & Opsi Jawaban
                Forms\Components\Section::make('Bank Soal Ujian')
                    ->description('Masukkan daftar soal beserta kunci jawabannya di bawah ini.')
                    ->schema([
                        Forms\Components\Repeater::make('questions')
                            ->relationship('questions')
                            ->label('Daftar Soal')
                            ->addActionLabel('Tambah Soal Baru')
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => isset($state['question_text']) ? strip_tags($state['question_text']) : 'Soal Baru')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label('Tipe Soal')
                                            ->options([
                                                'multiple_choice' => 'Pilihan Ganda',
                                                'essay' => 'Uraian / Essay',
                                            ])
                                            ->default('multiple_choice')
                                            ->reactive()
                                            ->required(),

                                        Forms\Components\TextInput::make('score_weight')
                                            ->label('Bobot Nilai')
                                            ->numeric()
                                            ->default(1.0)
                                            ->required(),

                                        Forms\Components\FileUpload::make('media_path')
                                            ->label('Gambar/Media Soal (Opsional)')
                                            ->image()
                                            ->directory('cbt-questions')
                                            ->visibility('public'),
                                    ]),

                                Forms\Components\RichEditor::make('question_text')
                                    ->label('Teks Soal')
                                    ->toolbarButtons([
                                        'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'codeBlock'
                                    ])
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\Repeater::make('options')
                                    ->relationship('options')
                                    ->label('Opsi Jawaban')
                                    ->addActionLabel('Tambah Opsi Jawaban')
                                    ->visible(fn (Forms\Get $get) => $get('type') === 'multiple_choice')
                                    ->schema([
                                        Forms\Components\TextInput::make('option_text')
                                            ->label('Teks Jawaban')
                                            ->requiredWithout('media_path'),

                                        Forms\Components\FileUpload::make('media_path')
                                            ->label('Gambar Opsi')
                                            ->image()
                                            ->directory('cbt-options'),

                                        Forms\Components\Toggle::make('is_correct')
                                            ->label('Kunci Jawaban?')
                                            ->inline(false)
                                            ->default(false),
                                    ])
                                    ->columns(3)
                                    ->grid(2)
                                    ->defaultItems(4),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Ujian')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (CbtExam $record) => "Mata Pelajaran: " . ($record->subject?->name ?? '-')),

                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('token')
                    ->label('Token')
                    ->badge()
                    ->color('warning')
                    ->copyable()
                    ->copyMessage('Token disalin!'),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Durasi')
                    ->suffix(' Mnt')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Jml Soal')
                    ->counts('questions')
                    ->badge()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Pelaksanaan')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->description(fn (CbtExam $record) => "s/d " . $record->end_time?->format('d M Y, H:i')),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),

                Tables\Filters\SelectFilter::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->relationship('subject', 'name'),
            ])
            ->actions([

                // ... di dalam method actions([...]) pada CbtExamResource.php

                Action::make('importQuestions')
                    ->label('Impor Soal')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->modalHeading('Impor Soal dari File Excel / CSV')
                    ->modalDescription('Pastikan susunan kolom pada file unggahan sesuai dengan ketentuan di bawah ini.')
                    ->form([
                        // Informasi / Panduan Format Kolom
                        Placeholder::make('format_info')
                            ->label('Panduan Format Kolom (Header)')
                            ->content(new HtmlString('
                                <div class="text-xs space-y-2 text-gray-600 dark:text-gray-300">
                                    <p>File Excel/CSV <strong>wajib</strong> memiliki baris pertama (header) dengan nama kolom berikut:</p>
                                    <ul class="list-disc list-inside space-y-1 font-mono text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 p-2 rounded">
                                        <li><strong>pertanyaan</strong> : Teks soal/pertanyaan</li>
                                        <li><strong>tipe</strong> : <code class="text-primary-600">multiple_choice</code> atau <code class="text-primary-600">essay</code></li>
                                        <li><strong>bobot</strong> : Angka bobot nilai (Contoh: 1)</li>
                                        <li><strong>opsi_a, opsi_b, opsi_c, opsi_d, opsi_e</strong> : Pilihan jawaban (Diisi jika tipe = multiple_choice)</li>
                                        <li><strong>kunci_jawaban</strong> : Huruf kunci (Contoh: <code class="text-emerald-600">A</code> / <code class="text-emerald-600">B</code> / <code class="text-emerald-600">C</code>)</li>
                                    </ul>
                                    <p class="italic text-amber-600 dark:text-amber-400">*Catatan: Jika PG ada 3 opsi, penulisan soal opsi  cukup dikosongkan dengan tanda petik contohnya <strong> "1","2","3","","", </strong></p>
                                    <p class="italic text-amber-600 dark:text-amber-400">*Catatan: Untuk tipe <strong>essay</strong>, kolom opsi_a s/d opsi_e cukup dikosongkan saja.</p>
                                </div>
                            ')),

                        FileUpload::make('excel_file')
                            ->label('File Template Excel / CSV')
                            ->disk('local')
                            ->directory('imports')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                                'text/plain',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, CbtExam $record) {
                        try {
                            $filePath = Storage::disk('local')->path($data['excel_file']);

                            Excel::import(new CbtQuestionImport($record->id), $filePath);

                            Notification::make()
                                ->title('Impor Berhasil')
                                ->body("Soal berhasil ditambahkan ke ujian: {$record->title}")
                                ->success()
                                ->send();
                        } catch (\Throwable $th) {
                            Notification::make()
                                ->title('Gagal Impor Soal')
                                ->body('Terjadi kesalahan: ' . $th->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCbtExams::route('/'),
            'create' => Pages\CreateCbtExam::route('/create'),
            'edit' => Pages\EditCbtExam::route('/{record}/edit'),
        ];
    }
}