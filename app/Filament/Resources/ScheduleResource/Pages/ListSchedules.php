<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Filament\Resources\ScheduleResource;
use App\Models\ClassRoom;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // TOMBOL CETAK LAPORAN JADWAL PELAJARAN BERDASARKAN KELAS
            Actions\Action::make('printSchedulePdf')
                ->label('Cetak Jadwal PDF')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('class_id')
                        ->label('Pilih Kelas')
                        ->options(function () {
                            $user = Auth::user();
                            $query = ClassRoom::query();
                            $userRole = $user->role ?? $user->role_type ?? '';

                            // Batasi jika user adalah guru
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
                ])
                ->modalHeading('Cetak Dokumen Jadwal Pelajaran')
                ->modalDescription('Silakan pilih kelas yang jadwal pelajarannya ingin dicetak ke dalam bentuk PDF.')
                ->modalSubmitActionLabel('Buka PDF Laporan')
                ->action(function (array $data) {
                    $url = route('pdf.schedules', [
                        'class_id' => $data['class_id'],
                    ]);

                    // Membuka PDF di tab baru browser
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