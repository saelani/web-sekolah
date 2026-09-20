<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\SchoolProfile;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentAttendancePdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:acad_classes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        Carbon::setLocale('id');

        $class = ClassRoom::with('homeroomTeacher')->findOrFail($request->class_id);
        $headmaster = Teacher::query()->where('role_type', 'headmaster')->first();
        $school = SchoolProfile::first();

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Hitung Hari Efektif (Hanya Senin s.d. Jumat, mengabaikan Sabtu & Minggu)
        $effectiveDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // isWeekday() bernilai true jika Senin sampai Jumat (mengabaikan Sabtu/Minggu)
            if ($currentDate->isWeekday()) {
                $effectiveDays++;
            }
            $currentDate->addDay();
        }

        $students = Student::where('class_id', $request->class_id)
            ->orderBy('id', 'asc')
            ->get();

        $attendances = StudentAttendance::where('class_id', $request->class_id)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->get();

        // Rekapitulasi per siswa & Hitung Persentase Kehadiran
        $summary = $students->map(function ($student) use ($attendances, $effectiveDays) {
            $studentAttendances = $attendances->where('student_id', $student->id);

            $sakit = $studentAttendances->whereIn('status', ['Sakit', 'S'])->count();
            $izin = $studentAttendances->whereIn('status', ['Izin', 'I'])->count();
            $alpa = $studentAttendances->whereIn('status', ['Alpa', 'A'])->count();

            $totalKetidakhadiran = $sakit + $izin + $alpa;

            // Hadir adalah total hari efektif (tanpa Sabtu/Minggu) dikurangi total ketidakhadiran
            $hadir = max(0, $effectiveDays - $totalKetidakhadiran);

            // Hitung persentase kehadiran
            $percentage = $effectiveDays > 0 ? round(($hadir / $effectiveDays) * 100, 1) : 0;

            return [
                'student' => $student,
                'hadir' => $hadir,
                'sakit' => $sakit,
                'izin' => $izin,
                'alpa' => $alpa,
                'total' => $totalKetidakhadiran,
                'percentage' => $percentage,
            ];
        });

        $titiMangsa = $endDate->translatedFormat('d F Y');
        $monthName = $startDate->translatedFormat('F Y');

        $pdf = Pdf::loadView('pdf.laporan-presensi-siswa', [
            'class' => $class,
            'attendances' => $attendances,
            'summary' => $summary,
            'startDate' => $request->start_date,
            'endDate' => $request->end_date,
            'effectiveDays' => $effectiveDays, // Otomatis tanpa Sabtu & Minggu
            'monthName' => $monthName,
            'school' => $school,
            'headmaster' => $headmaster,
            'teacher' => $class->homeroomTeacher,
            'titiMangsa' => $titiMangsa,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Laporan_Persentase_Kehadiran_{$class->name}.pdf");
    }
}
