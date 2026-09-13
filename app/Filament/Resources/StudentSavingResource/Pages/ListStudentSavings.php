<?php

namespace App\Filament\Resources\StudentSavingResource\Pages;

use App\Filament\Resources\StudentSavingResource;
use App\Models\ClassRoom;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListStudentSavings extends ListRecords
{
    protected static string $resource = StudentSavingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. Tombol Input Tabungan Massal bawaan kamu
            Actions\Action::make('batchInput')
                ->label('Input Tabungan Massal')
                ->icon('heroicon-o-pencil-square')
                ->color('success')
                ->url(StudentSavingResource::getUrl('batch-saving')),

            // 2. Tombol Cetak & Preview Laporan Tabungan (Landscape)
            Actions\Action::make('printSavingReport')
                ->label('Laporan Tabungan')
                ->icon('heroicon-o-printer')
                ->color('danger')
                ->form([
                    Forms\Components\Select::make('class_id')
                        ->label('Pilih Kelas')
                        ->options(function () {
                            $user = Auth::user();
                            $query = ClassRoom::query();
                            $userRole = $user->role ?? $user->role_type ?? '';

                            // Filter kelas berdasarkan role user (Guru hanya bisa akses kelasnya sendiri)
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
                        ->exists('acad_classes', 'id')
                        ->required(),

                    Forms\Components\DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->default(now()->startOfMonth())
                        ->required(),

                    Forms\Components\DatePicker::make('end_date')
                        ->label('Tanggal Selesai')
                        ->default(now()->endOfMonth())
                        ->required(),
                ])
                ->modalHeading('Filter Laporan Tabungan Siswa')
                ->modalSubmitActionLabel('Buka PDF Laporan')
                ->action(function (array $data) {
                    $url = route('student-saving.pdf', [
                        'class_id'   => $data['class_id'],
                        'start_date' => $data['start_date'],
                        'end_date'   => $data['end_date'],
                    ]);

                    // Peluncur link JS sintetis untuk membuka tab baru secara langsung tanpa terblokir popup blocker
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

            // 3. Tombol Tambah Data Single bawaan kamu
            Actions\CreateAction::make(),
        ];
    }
}