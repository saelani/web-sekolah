<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\StudentSaving;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\CbtExam;
use App\Models\CbtExamSession;
use App\Models\CbtStudentAnswer;
use App\Models\Enrollment;
use App\Models\AcademicYear;

class Dashboard extends Component
{
    // Navigasi Tab ('dashboard', 'materi', 'ujian', 'jadwal')
    public $activeTab = 'dashboard';

    // Filter Dropdown Mapel (Tab Materi & Ujian)
    public $selectedSubject = '';

    // Data Siswa & Status Preview Admin
    public $currentStudent;
    public bool $isAdminPreview = false;

    // Properti CBT Engine
    public bool $isTakingExam = false;
    public ?CbtExam $activeExam = null;
    public ?CbtExamSession $activeSession = null;
    public array $questions = [];
    public array $userAnswers = [];
    public int $currentIndex = 0;
    public int $remainingSeconds = 0;

    public function logout()
    {
        Auth::guard('student')->logout();
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return $this->redirect(route('student.login'), navigate: true);
    }

    private function resolveStudent()
    {
        $authUser = Auth::guard('student')->user() ?? Auth::user();
        $student = null;

        if ($authUser) {
            $student = $authUser->student ?? Student::where('user_id', $authUser->id)->first();
        }

        if (!$student) {
            $student = Student::first();
            $this->isAdminPreview = (bool) $student;
        } else {
            $this->isAdminPreview = false;
        }

        $this->currentStudent = $student;
        return $student;
    }

    // ==========================================
    // METHOD PENGERJAAN UJIAN CBT
    // ==========================================
    public function startExam($examId)
    {
        $studentUser = $this->resolveStudent();
        if (!$studentUser) return;

        $this->activeExam = CbtExam::with('questions')->findOrFail($examId);

        // Buat atau Ambil Sesi Ujian berdasarkan student_id
        $this->activeSession = CbtExamSession::firstOrCreate(
            [
                'cbt_exam_id' => $this->activeExam->id,
                'student_id'  => $studentUser->id,
            ],
            [
                'start_time'   => now(),
                'max_end_time' => now()->addMinutes($this->activeExam->duration_minutes ?? $this->activeExam->duration ?? 60),
                'status'       => 'ongoing',
            ]
        );

        // Cegah masuk jika ujian sudah disubmit / selesai
        if ($this->activeSession->status === 'completed' || $this->activeSession->status === 'submitted' || $this->activeSession->submitted_at) {
            return;
        }

        // Setup Soal
        $questionList = $this->activeExam->questions;
        if ($this->activeExam->randomize_questions) {
            $questionList = $questionList->shuffle();
        }
        $this->questions = $questionList->toArray();

        // Hitung Sisa Waktu (Detik) berdasarkan max_end_time
        $this->remainingSeconds = max(0, now()->diffInSeconds($this->activeSession->max_end_time, false));

        // Muat Jawaban yang Pernah Disimpan
        $existingAnswers = CbtStudentAnswer::where('cbt_exam_session_id', $this->activeSession->id)
            ->pluck('selected_answer', 'cbt_question_id');

        $this->userAnswers = [];
        foreach ($existingAnswers as $qId => $ans) {
            $this->userAnswers[$qId] = $ans;
        }

        $this->currentIndex = 0;
        $this->isTakingExam = true;
    }

    public function saveAnswer($questionId, $answer)
    {
        if (!$this->activeSession) return;

        $this->userAnswers[$questionId] = $answer;
        $question = collect($this->questions)->firstWhere('id', $questionId);
        
        $cleanAnswerKey = strtoupper(trim(substr((string) $answer, 0, 1)));
        $correctAnswerKey = strtoupper(trim((string) ($question['correct_answer'] ?? '')));

        $isCorrect = ($cleanAnswerKey === $correctAnswerKey) ? 1 : 0;

        CbtStudentAnswer::updateOrCreate(
            [
                'cbt_exam_session_id' => $this->activeSession->id,
                'cbt_question_id'     => $questionId,
            ],
            [
                'selected_answer' => $answer,
                'is_correct'      => $isCorrect,
            ]
        );
    }

    public function goToNext()
    {
        if ($this->currentIndex < count($this->questions) - 1) {
            $this->currentIndex++;
        }
    }

    public function goToPrevious()
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
        }
    }

    public function jumpToQuestion($index)
    {
        $this->currentIndex = $index;
    }

    public function submitExam()
    {
        if (!$this->activeSession) return;

        // Ambil semua jawaban yang tersimpan untuk sesi ini
        $answers = CbtStudentAnswer::where('cbt_exam_session_id', $this->activeSession->id)->get();
        
        $totalScoreWeight = array_sum(array_column($this->questions, 'score_weight')) ?: count($this->questions);
        $earnedScoreWeight = 0;

        foreach ($answers as $ans) {
            if ($ans->is_correct) {
                $question = collect($this->questions)->firstWhere('id', $ans->cbt_question_id);
                $earnedScoreWeight += $question['score_weight'] ?? 1;
            }
        }

        $finalScore = $totalScoreWeight > 0 ? round(($earnedScoreWeight / $totalScoreWeight) * 100, 2) : 0;

        $this->activeSession->update([
            'submitted_at' => now(),
            'total_score'  => $finalScore,
            'status'       => 'completed',
        ]);

        $this->isTakingExam = false;
        $this->activeExam = null;
        $this->activeSession = null;
        $this->activeTab = 'ujian';
    }

    public function render()
    {
        $user = Auth::guard('student')->user() ?? Auth::user();
        $studentUser = $this->resolveStudent();

        $totalSavings = 0;
        $recentSavings = collect();
        $attendances = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0];
        $cbtExams = collect();
        $schedules = collect();
        $assignments = collect();

        if ($studentUser) {
            $studentId = $studentUser->id;

            // 1. Data Tabungan & Kehadiran
            $totalSavings = StudentSaving::where('student_id', $studentId)->sum('amount');
            $recentSavings = StudentSaving::where('student_id', $studentId)
                ->latest()
                ->take(5)
                ->get();

            $attendances['hadir'] = StudentAttendance::where('student_id', $studentId)->where('status', 'hadir')->count();
            $attendances['sakit'] = StudentAttendance::where('student_id', $studentId)->where('status', 'sakit')->count();
            $attendances['izin']  = StudentAttendance::where('student_id', $studentId)->where('status', 'izin')->count();
            $attendances['alfa']  = StudentAttendance::where('student_id', $studentId)->where('status', 'alfa')->count();

            // 2. Fetch Enrollment Aktif Siswa
            $activeAcademicYear = AcademicYear::where('is_active', 1)->first();
            $enrollment = null;
            
            if ($activeAcademicYear) {
                $enrollment = Enrollment::where('student_id', $studentId)
                    ->where('academic_year_id', $activeAcademicYear->id)
                    ->first();
            }

            // 3. Fetch Ujian CBT
            $cbtExamsQuery = CbtExam::where('is_active', true);

            $cbtExamsQuery->with(['subject', 'sessions' => function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            }]);

            if (!empty($this->selectedSubject)) {
                $cbtExamsQuery->where('subject_id', $this->selectedSubject);
            }

            $cbtExams = $cbtExamsQuery->get();
        }

        // 4. Fetch Data Materi & Filter Subject
        $subjectsQuery = Subject::query();
        if (!empty($this->selectedSubject)) {
            $subjectsQuery->where('id', $this->selectedSubject);
        }

        $allSubjects = Subject::orderBy('name', 'asc')->get();

        return view('livewire.student.dashboard', [
            'user'          => $user,
            'student'       => $studentUser,
            'totalSavings'  => $totalSavings,
            'recentSavings' => $recentSavings,
            'attendances'   => $attendances,
            'allSubjects'   => $allSubjects,
            'cbtExams'      => $cbtExams,
            'schedules'     => $schedules,   
            'assignments'   => $assignments, 
        ])->layout('components.layouts.app');
    }
}