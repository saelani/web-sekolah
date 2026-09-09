<?php

namespace App\Filament\Widgets;

use App\Models\AcademicCalendar;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class AcademicCalendarWidget extends Widget
{
    protected static string $view = 'filament.widgets.academic-calendar-widget';

    // Lebar widget memenuhi layar
    protected int | string | array $columnSpan = 'full';

    public string $currentMonth;
    public string $currentYear;
    public ?int $selectedClassId = null;

    public function mount(): void
    {
        $this->currentMonth = now()->format('m');
        $this->currentYear = now()->format('Y');
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->format('m');
        $this->currentYear = $date->format('Y');
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->format('m');
        $this->currentYear = $date->format('Y');
    }

    public function goToToday(): void
    {
        $this->currentMonth = now()->format('m');
        $this->currentYear = now()->format('Y');
    }

    public function getViewData(): array
    {
        $startOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Cari hari pertama dalam grid (dimulai dari hari Senin)
        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        // Cari hari terakhir dalam grid (diakhiri hari Minggu)
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        // Ambil data agenda dari database untuk bulan ini
        $events = AcademicCalendar::with(['subject', 'classRoom', 'learningObjective'])
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('start_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                    ->orWhereBetween('end_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                    ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->where('start_date', '<=', $startOfMonth->toDateString())
                          ->where('end_date', '>=', $endOfMonth->toDateString());
                    });
            })
            ->when($this->selectedClassId, fn($q) => $q->where('class_room_id', $this->selectedClassId))
            ->get();

        // Buat matriks tanggal untuk grid 7 kolom (Senin - Minggu)
        $days = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dateString = $current->toDateString();

            // Filter event yang jatuh pada tanggal ini
            $dayEvents = $events->filter(function ($event) use ($dateString) {
                return $dateString >= $event->start_date->toDateString() && $dateString <= $event->end_date->toDateString();
            });

            $days[] = [
                'date'         => $current->copy(),
                'isCurrentMonth' => $current->month === (int) $this->currentMonth,
                'isToday'      => $current->isToday(),
                'events'       => $dayEvents,
            ];

            $current->addDay();
        }

        return [
            'days'        => $days,
            'monthName'   => $startOfMonth->translatedFormat('F Y'),
            'classes'     => \App\Models\ClassRoom::pluck('name', 'id')->toArray(),
        ];
    }
}