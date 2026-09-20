<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\CbtExam;
use App\Models\CbtExamSession;
use App\Models\Material;
use App\Models\Schedule;
use App\Models\Student; // <-- Pastikan model ini di-import
use App\Models\StudentAttendance;
use App\Models\StudentSaving;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $authUser = Auth::user();
        $student = $authUser->student ?? Student::where('user_id', $authUser->id)->first();

        if (! $student) {
            return response()->json(['message' => 'Data siswa tidak ditemukan'], 404);
        }

        $studentId = $student->id;
        $classId = $student->class_id;

        $totalSavings = StudentSaving::where('student_id', $studentId)->sum('amount');
        $recentSavings = StudentSaving::where('student_id', $studentId)->latest()->take(5)->get();

        $attendances = [
            'hadir' => StudentAttendance::where('student_id', $studentId)->where('status', 'hadir')->count(),
            'sakit' => StudentAttendance::where('student_id', $studentId)->where('status', 'sakit')->count(),
            'izin' => StudentAttendance::where('student_id', $studentId)->where('status', 'izin')->count(),
            'alfa' => StudentAttendance::where('student_id', $studentId)->where('status', 'alfa')->count(),
        ];

        // Ambil semua ujian aktif
        // Ambil semua ujian aktif
        $cbtExams = CbtExam::where('is_active', true)->with(['subject'])->get();

        // Ambil atau buat sesi secara aman agar tidak memicu error data kosong
        foreach ($cbtExams as $exam) {
            $session = CbtExamSession::firstOrCreate(
                [
                    'cbt_exam_id' => $exam->id,
                    'student_id' => $studentId,
                ],
                [
                    'status' => 'not_started',
                ]
            );

            // Masukkan data sesi ke relasi agar terbaca di JSON response Flutter
            $exam->setRelation('sessions', collect([$session]));
        }

        $materialsQuery = Material::where('is_active', true);
        if (isset($student->class_level)) {
            $materialsQuery->where('class_level', $student->class_level);
        }
        $materials = $materialsQuery->with('subject')->latest()->get();

        $rawSchedules = Schedule::with('subject')->where('class_id', $classId)->get();
        $assignments = Assignment::with('subject')->where('class_id', $classId)->orderBy('due_date', 'asc')->get();

        return response()->json([
            'student' => $student,
            'totalSavings' => $totalSavings,
            'recentSavings' => $recentSavings,
            'attendances' => $attendances,
            'cbtExams' => $cbtExams,
            'materials' => $materials,
            'rawSchedules' => $rawSchedules,
            'assignments' => $assignments,
        ]);
    }
}
