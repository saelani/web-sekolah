<?php

namespace App\Filament\Widgets;

use App\Models\AcademicCalendar;
use App\Models\ClassRoom;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class AcademicCalendarWidget extends Widget
{
    protected static string $view = 'filament.widgets.academic-calendar-widget';

    // Lebar widget memenuhi layar
    protected int|string|array $columnSpan = 'full';

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
        $date = Carbon::createFromDate((int) $this->currentYear, (int) $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->format('m');
        $this->currentYear = $date->format('Y');
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate((int) $this->currentYear, (int) $this->currentMonth, 1)->subMonth();
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
        $startOfMonth = Carbon::createFromDate((int) $this->currentYear, (int) $this->currentMonth, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Cari hari pertama dalam grid (Senin) & hari terakhir (Minggu)
        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        // OPTIMASI QUERY: Ambil event berdasarkan rentang grid tampilan ($startDate sampai $endDate)
        $events = AcademicCalendar::with(['subject', 'classRoom', 'learningObjective'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhereBetween('end_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate->toDateString())
                            ->where('end_date', '>=', $endDate->toDateString());
                    });
            })
            ->when($this->selectedClassId, fn ($q) => $q->where('class_room_id', $this->selectedClassId))
            ->get();

        // Matriks tanggal untuk 7 kolom (Senin - Minggu)
        $days = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $dateString = $current->toDateString();

            // Filter event yang jatuh pada tanggal ini
            $dayEvents = $events->filter(function ($event) use ($dateString) {
                $start = $event->start_date instanceof Carbon ? $event->start_date->toDateString() : Carbon::parse($event->start_date)->toDateString();
                $end = $event->end_date instanceof Carbon ? $event->end_date->toDateString() : Carbon::parse($event->end_date)->toDateString();

                return $dateString >= $start && $dateString <= $end;
            });

            $days[] = [
                'date' => $current->copy(),
                'dateString' => $dateString,
                'dayNumber' => $current->format('d'),
                'isCurrentMonth' => $current->month === (int) $this->currentMonth,
                'isToday' => $current->isToday(),
                'events' => $dayEvents,
            ];

            $current->addDay();
        }

        return [
            'days' => $days,
            'monthName' => $startOfMonth->translatedFormat('F Y'),
            'classes' => ClassRoom::pluck('name', 'id')->toArray(),
        ];
    }
}
