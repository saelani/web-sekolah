<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class StudentSavingPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'class_id'   => 'required|exists:acad_classes,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        $class = ClassRoom::with('homeroomTeacher')->findOrFail($request->class_id);

        $headmaster = Teacher::query()
            ->where('role_type', 'headmaster')
            ->first();

        $school = SchoolProfile::first();

        // Ambil data siswa beserta riwayat tabungan (sebelum dan pada periode ini)
        $students = Student::where('class_id', $request->class_id)
            ->with(['savings' => function ($q) use ($request) {
                $q->where('date', '<=', $request->end_date);
            }])
            ->orderBy('name', 'asc')
            ->get();

        // Rekapitulasi per siswa
        $summary = $students->map(function ($student) use ($request) {
            // 1. Saldo s/d Bulan Lalu (sebelum start_date)
            $prevSavings = $student->savings->where('date', '<', $request->start_date);
            $prevIn  = $prevSavings->where('type', 'in')->sum('amount');
            $prevOut = $prevSavings->where('type', 'out')->sum('amount');
            $saldoLalu = $prevIn - $prevOut;

            // 2. Jumlah Sekarang / Setor Bulan Ini (dalam rentang start_date - end_date)
            $currentSavings = $student->savings->whereBetween('date', [$request->start_date, $request->end_date]);
            $setorSekarang = $currentSavings->where('type', 'in')->sum('amount');

            // 3. Total s/d Bulan Ini
            $totalSekarang = $saldoLalu + $setorSekarang;

            return [
                'nisn'           => $student->nisn ?? '-',
                'name'           => $student->name,
                'saldo_lalu'     => $saldoLalu,
                'setor_sekarang' => $setorSekarang,
                'total_sekarang' => $totalSekarang,
            ];
        });

        $pdf = Pdf::loadView('pdf.laporan-tabungan-siswa', [
            'class'              => $class,
            'summary'            => $summary,
            'startDate'          => $request->start_date,
            'endDate'            => $request->end_date,
            'school'             => $school,
            'headmaster'         => $headmaster,
            'teacher'            => $class->homeroomTeacher,
            'grandTotalLalu'     => $summary->sum('saldo_lalu'),
            'grandSetorSekarang' => $summary->sum('setor_sekarang'),
            'grandTotalSekarang' => $summary->sum('total_sekarang'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_Tabungan_Kelas_{$class->name}.pdf");
    }
}