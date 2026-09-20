<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Models\Schedule;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Akademik SD';

    protected static ?string $navigationLabel = 'Jadwal Pelajaran';

    protected static ?string $modelLabel = 'Jadwal';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pengaturan Jadwal Pelajaran')
                    ->description('Tentukan hari, kelas, mata pelajaran, serta alokasi waktu jam pelajaran (memperhitungkan waktu istirahat sekolah).')
                    ->headerActions([
                        Forms\Components\Actions\Action::make('reset_form')
                            ->label('Reset Form')
                            ->color('gray')
                            ->icon('heroicon-o-arrow-path')
                            ->requiresConfirmation()
                            ->modalHeading('Reset Formulir?')
                            ->modalDescription('Semua pilihan kelas, mapel, dan inputan jadwal akan dikosongkan.')
                            ->action(function (Set $set) {
                                session()->forget(['last_schedule_class_id', 'last_schedule_subject_id']);
                                $set('class_id', null);
                                $set('subject_id', null);
                                $set('day_name', 'Senin');
                                $set('room_name', 'Ruang Kelas 5');
                                $set('start_period', 1);
                                $set('total_jp', 2);
                                $set('time_info', null);
                            }),
                    ])
                    ->schema([
                        Forms\Components\Select::make('class_id')
                            ->label('Kelas')
                            ->relationship('classRoom', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->default(fn () => session('last_schedule_class_id'))
                            ->afterStateUpdated(fn ($state) => session(['last_schedule_class_id' => $state])),

                        Forms\Components\Select::make('subject_id')
                            ->label('Mata Pelajaran')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->default(fn () => session('last_schedule_subject_id'))
                            ->afterStateUpdated(fn ($state) => session(['last_schedule_subject_id' => $state])),

                        Forms\Components\Select::make('day_name')
                            ->label('Hari')
                            ->options([
                                'Senin' => 'Senin',
                                'Selasa' => 'Selasa',
                                'Rabu' => 'Rabu',
                                'Kamis' => 'Kamis',
                                'Jumat' => 'Jumat',
                            ])
                            ->required()
                            ->default('Senin')
                            ->native(false),

                        Forms\Components\TextInput::make('room_name')
                            ->label('Nama / Nomor Ruangan')
                            ->maxLength(50)
                            ->default('Ruang Kelas 5'),

                        // --- SKENARIO PEMILIHAN JAM KE- & MASTER WAKTU DENGAN JEDA ISTIRAHAT ---
                        Forms\Components\Select::make('start_period')
                            ->label('Mulai Jam Ke-')
                            ->options([
                                1 => 'Jam ke-1 (06:30 - 07:05)',
                                2 => 'Jam ke-2 (07:05 - 07:40)',
                                3 => 'Jam ke-3 (07:40 - 08:15)',
                                4 => 'Jam ke-4 (08:15 - 08:50)',
                                // 08:50 - 09:05 -> Istirahat I (15 Menit)
                                5 => 'Jam ke-5 (09:05 - 09:40) [Selepas Istirahat I]',
                                6 => 'Jam ke-6 (09:40 - 10:15)',
                                7 => 'Jam ke-7 (10:15 - 10:50)',
                                // 10:50 - 11:05 -> Istirahat II / Dzuhur (15 Menit)
                                8 => 'Jam ke-8 (11:05 - 11:40) [Selepas Istirahat II]',
                                9 => 'Jam ke-9 (11:40 - 12:15)',
                            ])
                            ->default(1)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateScheduleTime($get, $set)),

                        Forms\Components\TextInput::make('total_jp')
                            ->label('Jumlah JP ( @ 35 Menit )')
                            ->numeric()
                            ->default(2)
                            ->minValue(1)
                            ->maxValue(9)
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateScheduleTime($get, $set)),

                        Forms\Components\Placeholder::make('time_info')
                            ->label('Estimasi Jam (Otomatis)')
                            ->content(function (Get $get, Set $set) {
                                self::calculateScheduleTime($get, $set);

                                return $get('time_info') ?? 'Pilih Jam Ke- dan Jumlah JP untuk melihat waktu mulai & selesai.';
                            })
                            ->columnSpanFull(),

                        // Hidden input untuk menyimpan format jam asli ke database
                        Forms\Components\Hidden::make('start_time'),
                        Forms\Components\Hidden::make('end_time'),
                    ])->columns(3),
            ]);
    }

    /**
     * Helper: Menghitung jam mulai dan selesai secara akurat berdasarkan master waktu sekolah (termasuk jeda istirahat)
     */
    protected static function calculateScheduleTime(Get $get, Set $set): void
    {
        $startPeriod = (int) $get('start_period');
        $jp = (int) $get('total_jp');

        if (! $startPeriod || ! $jp) {
            return;
        }

        // Master waktu definitif sekolah dasar (memasukkan jeda istirahat 15 menit)
        $scheduleMaster = [
            1 => ['start' => '06:30:00', 'end' => '07:05:00'],
            2 => ['start' => '07:05:00', 'end' => '07:40:00'],
            3 => ['start' => '07:40:00', 'end' => '08:15:00'],
            4 => ['start' => '08:15:00', 'end' => '08:50:00'],
            // Jeda Istirahat I: 08:50 - 09:05 (15 Menit) tidak dihitung sebagai jam pelajaran KBM
            5 => ['start' => '09:05:00', 'end' => '09:40:00'],
            6 => ['start' => '09:40:00', 'end' => '10:15:00'],
            7 => ['start' => '10:15:00', 'end' => '10:50:00'],
            // Jeda Istirahat II / Dzuhur: 10:50 - 11:05 (15 Menit)
            8 => ['start' => '11:05:00', 'end' => '11:40:00'],
            9 => ['start' => '11:40:00', 'end' => '12:15:00'],
        ];

        $endPeriod = $startPeriod + $jp - 1;

        if (isset($scheduleMaster[$startPeriod])) {
            $startTime = $scheduleMaster[$startPeriod]['start'];

            if (isset($scheduleMaster[$endPeriod])) {
                $endTime = $scheduleMaster[$endPeriod]['end'];
            } else {
                // Fallback jika melebihi slot master
                $endTime = Carbon::parse($startTime)->addMinutes($jp * 35)->format('H:i:s');
            }

            // Set nilai ke database
            $set('start_time', $startTime);
            $set('end_time', $endTime);

            // Tampilkan informasi visual ke placeholder
            $displayStart = substr($startTime, 0, 5);
            $displayEnd = substr($endTime, 0, 5);
            $labelPeriod = ($startPeriod == $endPeriod) ? "Jam Ke-{$startPeriod}" : "Jam Ke-{$startPeriod} s/d {$endPeriod}";

            $set('time_info', "⏱️ {$labelPeriod} | Waktu: {$displayStart} - {$displayEnd} WIB ({$jp} JP)");
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('classRoom.name')
                    ->label('Kelas')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('day_name')
                    ->label('Hari')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam Mulai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Jam Selesai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('room_name')
                    ->label('Ruangan')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('classRoom', 'name'),

                Tables\Filters\SelectFilter::make('day_name')
                    ->label('Hari')
                    ->options([
                        'Senin' => 'Senin',
                        'Selasa' => 'Selasa',
                        'Rabu' => 'Rabu',
                        'Kamis' => 'Kamis',
                        'Jumat' => 'Jumat',
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
            ])
            ->defaultSort('day_name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}
