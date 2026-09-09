<?php
namespace App\Filament\Resources\StudentSavingResource\Pages;

use App\Filament\Resources\StudentSavingResource;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\StudentSaving;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class BatchStudentSaving extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = StudentSavingResource::class;

    protected static string $view = 'filament.resources.student-saving-resource.pages.batch-student-saving';

    protected static ?string $title = 'Input Tabungan Massal Siswa';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'date' => now()->format('Y-m-d'),
            'type' => 'in',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Parameter Transaksi')
                    ->schema([
                        Select::make('class_id')
                            ->label('Kelas')
                            ->options(function () {
                                $user = Auth::user();
                                $query = ClassRoom::query();
                                $userRole = $user->role ?? $user->role_type ?? '';

                                if (!in_array($userRole, ['admin', 'headmaster', 'super_admin']) && !($user->is_admin ?? false)) {
                                    $teacherId = $user->teacher?->id ?? $user->teacher_id;
                                    if ($teacherId) {
                                        $query->where('teacher_id', $teacherId);
                                    } else {
                                        return [];
                                    }
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => $this->loadStudentsByClassId($get('class_id'), $set))
                            ->required(),

                        DatePicker::make('date')
                            ->label('Tanggal Transaksi')
                            ->default(now())
                            ->required(),

                        Select::make('type')
                            ->label('Jenis Transaksi')
                            ->options([
                                'in'  => 'Setor (Masuk)',
                                'out' => 'Tarik (Keluar)',
                            ])
                            ->default('in')
                            ->required(),
                    ])->columns(3),

                Section::make('Daftar Nominal Tabungan Siswa')
                    ->schema([
                        Repeater::make('students')
                            ->label('Daftar Siswa')
                            ->itemLabel(fn (array $state): ?string => $state['student_name'] ?? 'Siswa')
                            ->collapsible()
                            ->grid([
                                'default' => 1,
                                'md'      => 2,
                            ])
                            ->schema([
                                Hidden::make('student_id'),

                                TextInput::make('amount')
                                    ->label('Nominal Transaksi (Rp)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->placeholder('Misal: 5000')
                                    ->columnSpan(1),

                                TextInput::make('description')
                                    ->label('Keterangan (Opsional)')
                                    ->placeholder('Misal: Tabungan Harian')
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->extraAttributes([
                                'class' => '[&_.fi-fo-repeater-item]:p-3 [&_.fi-fo-repeater-item-header]:py-1 [&_.fi-fo-repeater-item-header]:px-2 [&_.fi-fo-repeater-item-content]:p-2',
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Memuat daftar siswa berdasarkan $classId yang dipilih
     */
    protected function loadStudentsByClassId(?string $classId, Set $set): void
    {
        if (!$classId) {
            $set('students', []);
            return;
        }

        $students = Student::where('class_id', $classId)
            ->orderBy('name')
            ->get();

        $studentsData = $students->map(function ($student) {
            return [
                'student_id'   => $student->id,
                'student_name' => $student->name,
                'amount'       => null,
                'description'  => null,
            ];
        })->toArray();

        $set('students', $studentsData);
    }

    /**
     * Mereset input nominal siswa setelah data disimpan
     */
    protected function resetStudentAmounts(): void
    {
        if (isset($this->data['students']) && is_array($this->data['students'])) {
            foreach ($this->data['students'] as $key => $student) {
                $this->data['students'][$key]['amount'] = null;
                $this->data['students'][$key]['description'] = null;
            }
        }
    }

    public function save(): void
    {
        $formData = $this->form->getState();

        $date = $formData['date'] ?? now()->format('Y-m-d');
        $type = $formData['type'] ?? 'in';
        $students = $formData['students'] ?? [];

        if (empty($students)) {
            Notification::make()
                ->title('Gagal Menyimpan')
                ->body('Tidak ada data siswa yang diproses.')
                ->danger()
                ->send();
            return;
        }

        $countSaved = 0;

        foreach ($students as $student) {
            if (isset($student['amount']) && $student['amount'] !== '' && (float)$student['amount'] > 0) {
                StudentSaving::create([
                    'student_id'  => $student['student_id'],
                    'user_id'     => Auth::id(),
                    'date'        => $date,
                    'type'        => $type,
                    'amount'      => (float)$student['amount'],
                    'description' => $student['description'] ?? null,
                ]);
                $countSaved++;
            }
        }

        Notification::make()
            ->title('Berhasil Disimpan')
            ->body("Sebanyak {$countSaved} data transaksi tabungan berhasil dicatat.")
            ->success()
            ->send();

        // Reset nilai inputan nominal dan keterangan tanpa error Closure
        $this->resetStudentAmounts();
    }
}