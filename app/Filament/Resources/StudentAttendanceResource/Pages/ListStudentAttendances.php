<?php

namespace App\Filament\Resources\StudentAttendanceResource\Pages;

use App\Filament\Resources\StudentAttendanceResource;
use App\Models\ClassRoom;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ListStudentAttendances extends ListRecords
{
    protected static string $resource = StudentAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('batchAttendance')
                ->label('Input Presensi Massal')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->url(StudentAttendanceResource::getUrl('batch-attendance')),

            // TOMBOL CETAK LAPORAN BERDASARKAN BULAN
            Actions\Action::make('printReport')
                ->label('Laporan Presensi')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->form([
                    Forms\Components\Select::make('class_id')
                        ->label('Pilih Kelas')
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
                        ->required(),

                    // Filter Bulan
                    Forms\Components\Select::make('month')
                        ->label('Bulan')
                        ->options([
                            '1'  => 'Januari',
                            '2'  => 'Februari',
                            '3'  => 'Maret',
                            '4'  => 'April',
                            '5'  => 'Mei',
                            '6'  => 'Juni',
                            '7'  => 'Juli',
                            '8'  => 'Agustus',
                            '9'  => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember',
                        ])
                        ->default(now()->month)
                        ->required(),

                    // Filter Tahun
                    Forms\Components\Select::make('year')
                        ->label('Tahun')
                        ->options(function () {
                            $years = range(now()->year - 2, now()->year + 1);
                            return array_combine($years, $years);
                        })
                        ->default(now()->year)
                        ->required(),
                ])
                ->modalHeading('Filter Laporan Presensi Bulanan')
                ->modalSubmitActionLabel('Buka PDF Laporan')
                ->action(function (array $data) {
                    // Hitung otomatis tanggal awal dan akhir dari bulan & tahun yang dipilih
                    $startDate = Carbon::createFromDate($data['year'], $data['month'], 1)->startOfMonth()->format('Y-m-d');
                    $endDate = Carbon::createFromDate($data['year'], $data['month'], 1)->endOfMonth()->format('Y-m-d');

                    $url = route('student-attendance.pdf', [
                        'class_id'   => $data['class_id'],
                        'start_date' => $startDate,
                        'end_date'   => $endDate,
                    ]);

                    // Membuka PDF di tab baru
                    $this->js("
                        const a = document.createElement('a');
                        a.href = '{$url}';
                        a.target = '_blank';
                        a.rel = 'noopener noreferrer';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                    ");
                }),

            Actions\CreateAction::make(),
        ];
    }
}