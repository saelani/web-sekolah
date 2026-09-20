<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialResource\Pages;
use App\Models\ClassRoom;
use App\Models\Material;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Akademik SD';

    protected static ?string $navigationLabel = 'Materi Pembelajaran';

    protected static ?string $modelLabel = 'Materi';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Materi')
                    ->description('Masukkan informasi dasar mata pelajaran, kelas, dan jenis materi.')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Materi')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('subject')
                            ->label('Mata Pelajaran')
                            ->options(Subject::pluck('name', 'name'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('class_level')
                            ->label('Kelas')
                            ->options(ClassRoom::pluck('name', 'name'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('semester')
                            ->label('Semester')
                            ->options([
                                '1' => 'Semester 1 (Ganjil)',
                                '2' => 'Semester 2 (Genap)',
                            ])
                            ->required()
                            ->default('1'),

                        Forms\Components\Select::make('type')
                            ->label('Jenis Materi')
                            ->options([
                                'summary' => 'Ringkasan Teks',
                                'pdf' => 'File PDF',
                                'image' => 'Gambar / Foto',
                                'link' => 'Link Eksternal',
                                'youtube' => 'Video YouTube',
                            ])
                            ->required()
                            ->live()
                            ->native(false),
                    ])->columns(['default' => 1, 'md' => 2]),

                Forms\Components\Section::make('Konten & Media Pembelajaran')
                    ->description('Unggah file atau masukkan tautan media sesuai dengan jenis materi yang dipilih.')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Isi Ringkasan / Catatan Teks')
                            ->nullable()
                            ->columnSpanFull(),

                        // Field FileUpload tunggal yang dinamis untuk menyimpan ke file_path tanpa bentrok
                        Forms\Components\FileUpload::make('file_path')
                            ->label(fn (callable $get) => $get('type') === 'pdf' ? 'Upload Dokumen PDF' : 'Upload Gambar / Foto Materi')
                            ->directory(fn (callable $get) => $get('type') === 'pdf' ? 'materials/pdf' : 'materials/images')
                            ->acceptedFileTypes(fn (callable $get) => $get('type') === 'pdf' ? ['application/pdf'] : ['image/jpeg', 'image/png', 'image/webp'])
                            ->image(fn (callable $get) => $get('type') === 'image')
                            ->imageEditor(fn (callable $get) => $get('type') === 'image')
                            ->maxSize(10240) // 10MB
                            ->visible(fn (callable $get) => in_array($get('type'), ['pdf', 'image']))
                            ->required(fn (callable $get) => in_array($get('type'), ['pdf', 'image']))
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('external_url')
                            ->label('URL / Link (YouTube atau Link Eksternal)')
                            ->url()
                            ->placeholder('https://www.youtube.com/watch?v=xxxx')
                            ->visible(fn (callable $get) => in_array($get('type'), ['link', 'youtube']))
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Tampilkan di Portal Siswa')
                            ->helperText('Nonaktifkan jika materi belum siap ditampilkan.')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul Materi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Mata Pelajaran')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('class_level')
                    ->label('Kelas')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('semester')
                    ->label('Semester')
                    ->formatStateUsing(fn ($state) => $state === '1' ? 'Sem 1 (Ganjil)' : 'Sem 2 (Genap)')
                    ->badge(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'primary' => 'summary',
                        'success' => 'pdf',
                        'warning' => 'image',
                        'info' => 'link',
                        'danger' => 'youtube',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'summary' => 'Ringkasan',
                        'pdf' => 'PDF',
                        'image' => 'Gambar',
                        'link' => 'Link',
                        'youtube' => 'YouTube',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('subject')
                    ->label('Mata Pelajaran')
                    ->options(Subject::pluck('name', 'name')),

                Tables\Filters\SelectFilter::make('class_level')
                    ->label('Kelas')
                    ->options(ClassRoom::pluck('name', 'name')),

                Tables\Filters\SelectFilter::make('semester')
                    ->label('Semester')
                    ->options([
                        '1' => 'Semester 1',
                        '2' => 'Semester 2',
                    ]),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Materi')
                    ->options([
                        'summary' => 'Ringkasan Teks',
                        'pdf' => 'File PDF',
                        'image' => 'Gambar / Foto',
                        'link' => 'Link Eksternal',
                        'youtube' => 'Video YouTube',
                    ]),
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
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
}
