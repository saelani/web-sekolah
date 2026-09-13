<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CbtExamResource\Pages;
use App\Imports\CbtQuestionImport;
use App\Models\CbtExam;
use App\Models\ClassRoom;
use App\Models\LearningObjective;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CbtExamResource extends Resource
{
    protected static ?string $model = CbtExam::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Manajemen CBT & Nilai';

    protected static ?string $navigationLabel = 'Ujian CBT';

    protected static ?string $modelLabel = 'Ujian CBT';

    protected static ?string $pluralModelLabel = 'Daftar Ujian CBT';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (($user->role_type ?? '') === 'headmaster' || ($user->is_admin ?? false)) {
            return $query;
        }

        $teacherId = $user->teacher?->id ?? $user->teacher_id;

        return $query->where('teacher_id', $teacherId);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                                FormAction::make('generateToken')
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
                // ------------------------------------------------------------------------
                // ACTION 1: BUAT SOAL DENGAN AI (BEBAS CIRCULAR BUG & RESPONSIF)
                // ------------------------------------------------------------------------
                TableAction::make('generateAiQuestions')
                    ->label('Buat Soal dengan AI')
                    ->icon('heroicon-o-sparkles')
                    ->color('purple')
                    ->modalHeading('✨ AI Question Generator (Kurikulum Merdeka)')
                    ->modalDescription('Pilih mata pelajaran untuk menampilkan TP dari database, lalu buat prompt AI.')
                    ->modalSubmitAction(false) // MATIKAN SUBMIT BAWAAN AGAR TIDAK MUTER
                    ->modalCancelActionLabel('Tutup')
                    ->form([
                        Forms\Components\Section::make('Konfigurasi Asesmen & Prompt')
                            ->schema([
                                Forms\Components\Select::make('assessment_type')
                                    ->label('Pilih Jenis Asesmen')
                                    ->options([
                                        'formatif' => '1. Asesmen Formatif (Harian/Proses)',
                                        'sumatif_tp' => '2. Asesmen Sumatif Lingkup Materi (SLM / Bab)',
                                        'sumatif_akhir' => '3. Asesmen Sumatif Akhir Semester (SAS)',
                                    ])
                                    ->default('formatif')
                                    ->required(),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('class_room_id')
                                            ->label('Kelas')
                                            ->options(fn () => ClassRoom::pluck('name', 'id')->toArray())
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Forms\Components\Select::make('subject_id')
                                            ->label('Mata Pelajaran')
                                            ->options(fn () => Subject::pluck('name', 'id')->toArray())
                                            ->default(fn (CbtExam $record) => $record->subject_id)
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->live(),
                                    ]),

                                Forms\Components\Select::make('learning_objective_id')
                                    ->label('Tujuan Pembelajaran (TP)')
                                    ->options(function (Forms\Get $get) {
                                        $subjectId = $get('subject_id');
                                        if (!$subjectId) {
                                            return [];
                                        }

                                        try {
                                            return LearningObjective::where('subject_id', $subjectId)
                                                ->get()
                                                ->pluck('description', 'id')
                                                ->map(fn ($desc, $id) => Str::limit($desc ?? "TP #{$id}", 90))
                                                ->toArray();
                                        } catch (\Throwable $e) {
                                            return [];
                                        }
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Pilih Mata Pelajaran terlebih dahulu untuk memuat daftar TP.')
                                    ->live(),

                                Forms\Components\Textarea::make('custom_learning_objective')
                                    ->label('Deskripsi TP Manual (Opsional)')
                                    ->placeholder('Isi manual di sini jika TP tidak dipilih...')
                                    ->rows(2),

                                Forms\Components\TextInput::make('question_count')
                                    ->label('Jumlah Soal')
                                    ->numeric()
                                    ->default(5)
                                    ->minValue(1)
                                    ->maxValue(20)
                                    ->required(),

                                Forms\Components\Actions::make([
                                    FormAction::make('generatePromptText')
                                        ->label('✨ Buat Prompt AI')
                                        ->button()
                                        ->color('primary')
                                        ->extraAttributes([
                                            'style' => 'background-color: #2563eb !important; color: #ffffff !important; font-weight: bold;',
                                        ])
                                        ->action(function (Forms\Get $get, Forms\Set $set) {
                                            $jenis = match($get('assessment_type')) {
                                                'formatif' => 'Asesmen Formatif Harian',
                                                'sumatif_tp' => 'Sumatif Lingkup Materi',
                                                default => 'Sumatif Akhir Semester',
                                            };

                                            $className = ClassRoom::find($get('class_room_id'))?->name ?? 'Kelas';
                                            $subjectName = Subject::find($get('subject_id'))?->name ?? 'Mata Pelajaran';

                                            $tpText = '';
                                            if ($get('learning_objective_id')) {
                                                $tpModel = LearningObjective::find($get('learning_objective_id'));
                                                $tpText = $tpModel?->description ?? '';
                                            }

                                            if (empty($tpText)) {
                                                $tpText = $get('custom_learning_objective') ?? '';
                                            }

                                            $jumlah = $get('question_count') ?? 5;

                                            $prompt = "Buatkan {$jumlah} soal pilihan ganda (4 opsi: A, B, C, D) untuk {$jenis} tingkat {$className}, Mata Pelajaran {$subjectName}.\n";
                                            $prompt .= "Tujuan Pembelajaran (TP): {$tpText}.\n\n";
                                            $prompt .= "WAJIB BALAS HANYA DALAM FORMAT JSON MURNI TANPA TEKS TAMBAHAN DENGAN STRUKTUR BERIKUT:\n";
                                            $prompt .= "[\n";
                                            $prompt .= "  {\n";
                                            $prompt .= "    \"pertanyaan\": \"Teks soal di sini\",\n";
                                            $prompt .= "    \"opsi_a\": \"Jawaban A\",\n";
                                            $prompt .= "    \"opsi_b\": \"Jawaban B\",\n";
                                            $prompt .= "    \"opsi_c\": \"Jawaban C\",\n";
                                            $prompt .= "    \"opsi_d\": \"Jawaban D\",\n";
                                            $prompt .= "    \"kunci_jawaban\": \"A\"\n";
                                            $prompt .= "  }\n";
                                            $prompt .= "]";

                                            $set('generated_prompt', $prompt);
                                        }),

                                    FormAction::make('copyPromptText')
                                        ->label('📋 Salin Prompt AI')
                                        ->button()
                                        ->color('success')
                                        ->extraAttributes([
                                            'style' => 'background-color: #059669 !important; color: #ffffff !important; font-weight: bold;',
                                        ])
                                        ->action(function (Forms\Get $get, $livewire) {
                                            $promptText = addslashes($get('generated_prompt') ?? '');

                                            $livewire->js("
                                                if (`{$promptText}`.trim() !== '') {
                                                    navigator.clipboard.writeText(`{$promptText}`).then(() => {
                                                        new FilamentNotification()
                                                            .title('Prompt AI Berhasil Disalin!')
                                                            .success()
                                                            .send();
                                                    });
                                                }
                                            ");
                                        }),
                                ]),

                                Forms\Components\Textarea::make('generated_prompt')
                                    ->label('Hasil Prompt AI')
                                    ->rows(4)
                                    ->readOnly()
                                    ->helperText('Klik "Buat Prompt AI", lalu klik "Salin Prompt AI" untuk menempelkannya ke ChatGPT/Gemini.'),
                            ]),

                        Forms\Components\Section::make('Hasil dari AI (Paste JSON)')
                            ->schema([
                                Forms\Components\Textarea::make('ai_json_output')
                                    ->label('Tempelkan Balasan JSON dari AI di Sini')
                                    ->placeholder("[\n  {\n    \"pertanyaan\": \"...\",\n    \"opsi_a\": \"...\",\n    ...\n  }\n]")
                                    ->rows(5)
                                    ->required(),

                                // TOMBOL SIMPAN KE DATABASE (TAMPIL KONTRAST & STABIL)
                                Forms\Components\Actions::make([
                                    FormAction::make('saveQuestionsFromModal')
                                        ->label('💾 Simpan Soal ke Database')
                                        ->button()
                                        ->color('primary')
                                        ->extraAttributes([
                                            'style' => 'background-color: #16a34a !important; color: #ffffff !important; font-weight: bold; width: 100%; padding: 10px;',
                                        ])
                                        ->action(function (Forms\Get $get, CbtExam $record, $livewire) {
                                            $jsonText = $get('ai_json_output');

                                            if (empty($jsonText)) {
                                                Notification::make()
                                                    ->title('JSON Masih Kosong')
                                                    ->body('Tempelkan teks JSON dari AI terlebih dahulu.')
                                                    ->warning()
                                                    ->send();
                                                return;
                                            }

                                            try {
                                                $questionsData = json_decode($jsonText, true);

                                                if (!is_array($questionsData)) {
                                                    throw new \Exception('Format JSON tidak valid.');
                                                }

                                                DB::transaction(function () use ($questionsData, $record) {
                                                    foreach ($questionsData as $q) {
                                                        $question = $record->questions()->create([
                                                            'type' => 'multiple_choice',
                                                            'question_text' => $q['pertanyaan'] ?? $q['question'] ?? 'Soal AI',
                                                            'score_weight' => 1.0,
                                                        ]);

                                                        $kunci = strtoupper(trim($q['kunci_jawaban'] ?? $q['correct_answer'] ?? 'A'));

                                                        $optionsMap = [
                                                            'A' => $q['opsi_a'] ?? $q['option_a'] ?? '',
                                                            'B' => $q['opsi_b'] ?? $q['option_b'] ?? '',
                                                            'C' => $q['opsi_c'] ?? $q['option_c'] ?? '',
                                                            'D' => $q['opsi_d'] ?? $q['option_d'] ?? '',
                                                        ];

                                                        foreach ($optionsMap as $key => $text) {
                                                            if (!empty($text)) {
                                                                $question->options()->create([
                                                                    'option_text' => $text,
                                                                    'is_correct' => ($key === $kunci),
                                                                ]);
                                                            }
                                                        }
                                                    }
                                                });

                                                Notification::make()
                                                    ->title('Soal AI Berhasil Ditambahkan!')
                                                    ->body(count($questionsData) . " soal baru telah dimasukkan ke ujian: {$record->title}")
                                                    ->success()
                                                    ->send();

                                                $livewire->mountTableAction('generateAiQuestions', $record->id);
                                            } catch (\Throwable $th) {
                                                Notification::make()
                                                    ->title('Gagal Memproses Soal AI')
                                                    ->body('Terjadi kesalahan: ' . $th->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),
                                ])->fullWidth(),
                            ]),
                    ]),

                // ------------------------------------------------------------------------
                // ACTION 2: IMPOR SOAL EXCEL / CSV
                // ------------------------------------------------------------------------
                TableAction::make('importQuestions')
                    ->label('Impor Soal')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->modalHeading('Impor Soal dari File Excel / CSV')
                    ->modalDescription('Pastikan susunan kolom pada file unggahan sesuai dengan ketentuan di bawah ini.')
                    ->form([
                        Placeholder::make('format_info')
                            ->label('Panduan Format Kolom (Header)')
                            ->content(new HtmlString('
                                <div class="text-xs space-y-2 text-gray-600 dark:text-gray-300">
                                    <p>File Excel/CSV <strong>wajib</strong> memiliki baris pertama (header) dengan nama kolom berikut:</p>
                                    <ul class="list-disc list-inside space-y-1 font-mono text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-800 p-2 rounded">
                                        <li><strong>pertanyaan</strong> : Teks soal/pertanyaan</li>
                                        <li><strong>tipe</strong> : <code class="text-primary-600">multiple_choice</code> atau <code class="text-primary-600">essay</code></li>
                                        <li><strong>bobot</strong> : Angka bobot nilai (Contoh: 1)</li>
                                        <li><strong>opsi_a, opsi_b, opsi_c, opsi_d, opsi_e</strong> : Pilihan jawaban</li>
                                        <li><strong>kunci_jawaban</strong> : Huruf kunci (Contoh: <code class="text-emerald-600">A</code> / <code class="text-emerald-600">B</code>)</li>
                                    </ul>
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