<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\SchoolProfile; // Sesuaikan jika nama model profil sekolah Anda berbeda
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class StudentAttendancePdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'class_id'   => 'required|exists:acad_classes,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        // 1. Ambil data Kelas dan Wali Kelas (homeroomTeacher)
        $class = ClassRoom::with('homeroomTeacher')->findOrFail($request->class_id);

        // 2. Ambil Kepala Sekolah berdasarkan enum role_type = 'headmaster'
        $headmaster = Teacher::query()
            ->where('role_type', 'headmaster')
            ->first();

        // 3. Ambil data Profil Sekolah
        $school = SchoolProfile::first();

        // 4. Query Data Presensi
        $attendances = StudentAttendance::with('student')
            ->where('class_id', $request->class_id)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->orderBy('date', 'asc')
            ->get();

        // 5. Rekapitulasi per siswa
        $summary = $attendances->groupBy('student_id')->map(function ($items) {
            return [
                'student' => $items->first()->student,
                'hadir'   => $items->whereIn('status', ['Hadir', 'H'])->count(),
                'sakit'   => $items->whereIn('status', ['Sakit', 'S'])->count(),
                'izin'    => $items->whereIn('status', ['Izin', 'I'])->count(),
                'alpa'    => $items->whereIn('status', ['Alpa', 'A'])->count(),
            ];
        });

        // 6. Generate PDF
        $pdf = Pdf::loadView('pdf.laporan-presensi-siswa', [
            'class'       => $class,
            'attendances' => $attendances,
            'summary'     => $summary,
            'startDate'   => $request->start_date,
            'endDate'     => $request->end_date,
            'school'      => $school,
            'headmaster'  => $headmaster,
            'teacher'     => $class->homeroomTeacher,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Laporan_Presensi_{$class->name}.pdf");
    }
}