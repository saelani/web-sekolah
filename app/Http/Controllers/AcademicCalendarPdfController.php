<?php

namespace App\Http\Controllers;

use App\Models\AcademicCalendar;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\SchoolProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AcademicCalendarPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'class_room_id' => 'required|exists:acad_classes,id',
            'semester'      => 'required|in:1,2',
            'year'          => 'required|numeric',
        ]);

        $class = ClassRoom::with('homeroomTeacher')->findOrFail($request->class_room_id);
        $subject = $request->subject_id ? Subject::find($request->subject_id) : null;
        $headmaster = Teacher::where('role_type', 'headmaster')->first();
        $school = SchoolProfile::first();

        $year = (int) $request->year;
        // Semester 1: Juli - Desember | Semester 2: Januari - Juni
        if ($request->semester == '1') {
            $startMonth = 7;
            $endMonth = 12;
            $monthsList = [
                7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $startDate = "{$year}-07-01";
            $endDate   = "{$year}-12-31";
        } else {
            $startMonth = 1;
            $endMonth = 6;
            $monthsList = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                4 => 'April', 5 => 'Mei', 6 => 'Juni'
            ];
            $startDate = "{$year}-01-01";
            $endDate   = "{$year}-06-30";
        }

        // Fetch Query
        $query = AcademicCalendar::with(['learningObjective', 'subject'])
            ->where('class_room_id', $request->class_room_id)
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        $events = $query->orderBy('start_date', 'asc')->get();

        // Pemetaan per Bulan & Minggu Ke (1-5)
        $monthlyMatrix = [];
        foreach ($monthsList as $mNum => $mName) {
            $monthlyMatrix[$mNum] = [
                'name' => $mName,
                'weeks' => [1 => [], 2 => [], 3 => [], 4 => [], 5 => []],
            ];
        }

        foreach ($events as $event) {
            $eventStart = Carbon::parse($event->start_date);
            $mNum = $eventStart->month;

            if (isset($monthlyMatrix[$mNum])) {
                // Hitung minggu ke berapa dalam bulan tersebut (1-5)
                $dayOfMonth = $eventStart->day;
                $weekNumber = (int) ceil($dayOfMonth / 7);
                if ($weekNumber > 5) $weekNumber = 5;

                $monthlyMatrix[$mNum]['weeks'][$weekNumber][] = $event;
            }
        }

        $pdf = Pdf::loadView('pdf.laporan-kalender-pembelajaran', [
            'class'         => $class,
            'subject'       => $subject,
            'semester'      => $request->semester,
            'year'          => $year,
            'monthlyMatrix' => $monthlyMatrix,
            'school'        => $school,
            'headmaster'    => $headmaster,
            'teacher'       => $class->homeroomTeacher,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("Pemetaan_Pembelajaran_{$class->name}.pdf");
    }
}