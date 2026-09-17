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
use App\Models\CbtOption;
use App\Models\Material; 
use App\Models\Schedule;     // <-- Tambahan Model Schedule
use App\Models\Assignment;   // <-- Tambahan Model Assignment
use App\Models\Enrollment;
use App\Models\AcademicYear;

class Dashboard extends Component
{
    public $activeTab = 'dashboard';
    public $selectedSubject = '';
    public $currentStudent;
    public bool $isAdminPreview = false;

    // Properti CBT Engine
    public bool $isTakingExam = false;
    public ?CbtExam $activeExam = null;
    public ?CbtExamSession $activeSession = null;
    public array $questions = [];
    public array $userAnswers = [];
    public array $essayAnswers = [];
    public int $currentIndex = 0;
    public int $remainingSeconds = 0;

    private function resolveStudent()
    {
        $authUser = Auth::guard('student')->user() ?? Auth::user();
        $student = null;

        if ($authUser) {
            $student = $authUser->student ?? Student::where('user_id', $authUser->id)->first();
        }

        if (! $student && $authUser && in_array($authUser->role ?? '', ['admin', 'teacher', 'headmaster'])) {
            $student = Student::first();
            $this->isAdminPreview = (bool) $student;
        } else {
            $this->isAdminPreview = false;
        }

        $this->currentStudent = $student;
        return $student;
    }

    public function startExam($examId)
    {
        $studentUser = $this->resolveStudent();
        if (! $studentUser) return;

        $this->activeExam = CbtExam::with(['questions.options'])->findOrFail($examId);

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

        if ($this->activeSession->status === 'completed' || $this->activeSession->status === 'submitted' || $this->activeSession->submitted_at) {
            return;
        }

        $questionCollection = $this->activeExam->questions;
        if ($this->activeExam->randomize_questions) {
            $questionCollection = $questionCollection->shuffle();
        }

        $this->questions = $questionCollection->map(function ($q) {
            $options = $q->options;
            if ($this->activeExam->randomize_options ?? false) {
                $options = $options->shuffle();
            }

            return [
                'id'            => $q->id,
                'type'          => $q->type ?? $q->question_type ?? (count($options) > 0 ? 'multiple_choice' : 'essay'),
                'question_text' => is_array($q->question_text) ? ($q->question_text['text'] ?? json_encode($q->question_text)) : $q->question_text,
                'score_weight'  => $q->score_weight ?? 1,
                'options'       => $options->map(function ($opt) {
                    return [
                        'id'          => $opt->id,
                        'option_text' => $opt->option_text ?? $opt->text ?? '',
                    ];
                })->toArray(),
            ];
        })->values()->toArray();

        $this->remainingSeconds = max(0, now()->diffInSeconds($this->activeSession->max_end_time, false));

        $existingAnswers = CbtStudentAnswer::where('cbt_exam_session_id', $this->activeSession->id)->get();

        $this->userAnswers = [];
        $this->essayAnswers = [];

        foreach ($this->questions as $q) {
            $qId = $q['id'];
            $saved = $existingAnswers->firstWhere('cbt_question_id', $qId);

            if ($saved) {
                if ($q['type'] === 'essay' || empty($q['options'])) {
                    $this->essayAnswers[$qId] = $saved->answer_text ?? '';
                    if (trim($saved->answer_text ?? '') !== '') {
                        $this->userAnswers[$qId] = 'essay_answered';
                    }
                } else {
                    $this->userAnswers[$qId] = $saved->cbt_option_id;
                }
            }
        }

        $this->currentIndex = 0;
        $this->isTakingExam = true;
    }

    public function saveAnswer($questionId, $optionId)
    {
        if (! $this->activeSession) return;

        $this->userAnswers[$questionId] = $optionId;

        $selectedOption = CbtOption::find($optionId);
        $isCorrect = $selectedOption ? (bool)$selectedOption->is_correct : false;

        CbtStudentAnswer::updateOrCreate(
            [
                'cbt_exam_session_id' => $this->activeSession->id,
                'cbt_question_id'     => $questionId,
            ],
            [
                'cbt_option_id' => $optionId,
                'answer_text'   => null,
                'is_correct'    => $isCorrect,
            ]
        );
    }

    public function saveEssayAnswer($questionId)
    {
        if (! $this->activeSession) return;

        $textAnswer = $this->essayAnswers[$questionId] ?? '';

        CbtStudentAnswer::updateOrCreate(
            [
                'cbt_exam_session_id' => $this->activeSession->id,
                'cbt_question_id'     => $questionId,
            ],
            [
                'cbt_option_id' => null,
                'answer_text'   => $textAnswer,
                'is_correct'    => null,
            ]
        );

        if (trim($textAnswer) !== '') {
            $this->userAnswers[$questionId] = 'essay_answered';
        } else {
            unset($this->userAnswers[$questionId]);
        }
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
        if (! $this->activeSession) return;

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

    public function convertToEmbedUrl($url)
    {
        if (empty($url)) return '';

        if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        if (preg_match('/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        return $url;
    }

    public function render()
    {
        $user = Auth::guard('student')->user() ?? Auth::user();
        $studentUser = $this->resolveStudent();

        $totalSavings = 0;
        $recentSavings = collect();
        $attendances = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alfa' => 0];
        $cbtExams = collect();
        $materials = collect(); 
        $rawSchedules = collect();
        $assignments = collect();

        // Master waktu standar sekolah dasar (1 JP = 35 Menit + Jeda Istirahat 15 Menit)
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

        $daysOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        if ($studentUser) {
            $studentId = $studentUser->id;
            $classId = $studentUser->class_id ?? null;

            $totalSavings = StudentSaving::where('student_id', $studentId)->sum('amount');
            $recentSavings = StudentSaving::where('student_id', $studentId)
                ->latest()
                ->take(5)
                ->get();

            $attendances['hadir'] = StudentAttendance::where('student_id', $studentId)->where('status', 'hadir')->count();
            $attendances['sakit'] = StudentAttendance::where('student_id', $studentId)->where('status', 'sakit')->count();
            $attendances['izin']  = StudentAttendance::where('student_id', $studentId)->where('status', 'izin')->count();
            $attendances['alfa']  = StudentAttendance::where('student_id', $studentId)->where('status', 'alfa')->count();

            // Query CBT Exams
            $cbtExamsQuery = CbtExam::where('is_active', true);
            $cbtExamsQuery->with(['subject', 'sessions' => function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            }]);

            if (! empty($this->selectedSubject)) {
                $cbtExamsQuery->where('subject_id', $this->selectedSubject);
            }
            $cbtExams = $cbtExamsQuery->get();

            // Query Materials
            $materialsQuery = Material::where('is_active', true);
            
            if (! empty($this->selectedSubject)) {
                $subjectModel = Subject::find($this->selectedSubject);
                if ($subjectModel) {
                    $materialsQuery->where('subject', 'like', '%' . $subjectModel->name . '%');
                }
            }

            if (isset($studentUser->class_level)) {
                $materialsQuery->where('class_level', $studentUser->class_level);
            }

            $materials = $materialsQuery->latest()->get();

            // Query Schedules & Assignments berdasarkan kelas siswa
            if ($classId) {
                $rawSchedules = Schedule::with('subject')
                    ->where('class_id', $classId)
                    ->get();

                $assignments = Assignment::with('subject')
                    ->where('class_id', $classId)
                    ->orderBy('due_date', 'asc')
                    ->get();
            }
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
            'materials'     => $materials,
            'masterTimes'   => $masterTimes,
            'daysOrder'     => $daysOrder,
            'rawSchedules'  => $rawSchedules, 
            'assignments'   => $assignments, 
        ])->layout('components.layouts.app');
    }
}