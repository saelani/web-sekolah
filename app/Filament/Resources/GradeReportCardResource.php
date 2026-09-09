<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeReportCardResource\Pages;
use App\Models\GradeReportCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GradeReportCardResource extends Resource
{
    protected static ?string $model = GradeReportCard::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Rapor';
    protected static ?string $navigationLabel = 'Cetak Rapor Siswa';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Rapor Siswa')
                    ->schema([
                        Forms\Components\Select::make('enrollment_id')
                            ->relationship('enrollment.student', 'name')
                            ->label('Siswa / Pendaftaran')
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('status')
                            ->label('Status Rapor')
                            ->options([
                                'draft'      => 'Draft',
                                'published'  => 'Diterbitkan / Siap Cetak',
                                'archived'   => 'Arsip',
                            ])
                            ->default('draft')
                            ->required(),

                        Forms\Components\DatePicker::make('issue_date')
                            ->label('Tanggal Pembagian Rapor')
                            ->default(now())
                            ->required(),

                        Forms\Components\Textarea::make('academic_note')
                            ->label('Catatan Akademik Wali Kelas')
                            ->placeholder('Masukkan catatan perkembangan belajar siswa...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('character_note')
                            ->label('Catatan Perkembangan Karakter / Sikap')
                            ->placeholder('Masukkan catatan perkembangan karakter siswa...')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('enrollment.student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),

                // Relasi ke Kelas via enrollment.class
                Tables\Columns\TextColumn::make('enrollment.class.name')
                    ->label('Kelas')
                    ->badge()
                    ->sortable(),

                // Relasi ke Tahun Ajaran via enrollment.academicYear
                Tables\Columns\TextColumn::make('enrollment.academicYear.year')
                    ->label('Tahun Ajaran')
                    ->sortable(),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Tanggal Cetak')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'draft'     => 'warning',
                        'archived'  => 'gray',
                        default     => 'gray',
                    }),
            ])
            ->filters([
                // Filter berdasarkan Kelas via enrollment
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('enrollment.class', 'name'),

                // Filter berdasarkan Tahun Ajaran via enrollment
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('enrollment.academicYear', 'year'),

                // Filter berdasarkan Status Rapor
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Rapor')
                    ->options([
                        'draft'     => 'Draft',
                        'published' => 'Diterbitkan / Siap Cetak',
                        'archived'  => 'Arsip',
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGradeReportCards::route('/'),
            'create' => Pages\CreateGradeReportCard::route('/create'),
            'edit'   => Pages\EditGradeReportCard::route('/{record}/edit'),
        ];
    }
}