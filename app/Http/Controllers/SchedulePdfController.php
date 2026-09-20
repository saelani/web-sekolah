<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Schedule;
use App\Models\SchoolProfile;
use App\Models\Teacher;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SchedulePdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:acad_classes,id',
        ]);

        Carbon::setLocale('id');

        $class = ClassRoom::with('homeroomTeacher')->findOrFail($request->class_id);
        $headmaster = Teacher::query()->where('role_type', 'headmaster')->first();
        $school = SchoolProfile::first();

        // Ambil data jadwal dari database untuk kelas ini
        $schedules = Schedule::with(['subject'])
            ->where('class_id', $request->class_id)
            ->get();

        // Mapping master waktu standar sekolah dasar (1 JP = 35 Menit + Istirahat)
        $masterTimes = [
            ['period' => '1', 'time' => '06:30 - 07:05', 'type' => 'lesson'],
            ['period' => '2', 'time' => '07:05 - 07:40', 'type' => 'lesson'],
            ['period' => '3', 'time' => '07:40 - 08:15', 'type' => 'lesson'],
            ['period' => '4', 'time' => '08:15 - 08:50', 'type' => 'lesson'],
            ['period' => '-', 'time' => '08:50 - 09:05', 'type' => 'break', 'label' => 'Istirahat I'],
            ['period' => '5', 'time' => '09:05 - 09:40', 'type' => 'lesson'],
            ['period' => '6', 'time' => '09:40 - 10:15', 'type' => 'lesson'],
            ['period' => '7', 'time' => '10:15 - 10:50', 'type' => 'lesson'],
            ['period' => '-', 'time' => '10:50 - 11:05', 'type' => 'break', 'label' => 'Istirahat II / Sholat Dzuhur'],
            ['period' => '8', 'time' => '11:05 - 11:40', 'type' => 'lesson'],
            ['period' => '9', 'time' => '11:40 - 12:15', 'type' => 'lesson'],
        ];

        // Hari operasional KBM diset hanya Senin s/d Jumat
        $daysOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $titiMangsa = Carbon::now()->translatedFormat('d F Y');

        $pdf = Pdf::loadView('pdf.laporan-jadwal-pelajaran', [
            'class' => $class,
            'schedules' => $schedules,
            'masterTimes' => $masterTimes,
            'daysOrder' => $daysOrder,
            'school' => $school,
            'headmaster' => $headmaster,
            'teacher' => $class->homeroomTeacher,
            'titiMangsa' => $titiMangsa,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Jadwal_Pelajaran_Detail_{$class->name}.pdf");
    }
}
