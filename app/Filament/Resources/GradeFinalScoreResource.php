<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GradeFinalScoreResource\Pages;
use App\Models\GradeFinalScore;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Services\GradeCalculationService;
use App\Traits\HasRoleScope;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Notifications\Notification;

class GradeFinalScoreResource extends Resource
{
    use HasRoleScope; // <-- getEloquentQuery() otomatis ter-override dari Trait!

    protected static ?string $model = GradeFinalScore::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Asesmen Intrakurikuler';
    protected static ?string $navigationLabel = 'Nilai Akhir & Deskripsi CP';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Rincian Nilai Rapor & Deskripsi Otomatis')
                    ->schema([
                        Select::make('enrollment_id')
                            ->relationship('enrollment.student', 'name')
                            ->disabled()
                            ->label('Siswa'),

                        Select::make('subject_id')
                            ->relationship('subject', 'name')
                            ->disabled()
                            ->label('Mata Pelajaran'),

                        TextInput::make('formative_avg')
                            ->numeric()
                            ->label('Rata-rata Formatif'),

                        TextInput::make('sumative_slm_avg')
                            ->numeric()
                            ->label('Rata-rata Sumatif SLM'),

                        TextInput::make('sumative_sas')
                            ->numeric()
                            ->label('Nilai SAS/SAT'),

                        TextInput::make('final_score')
                            ->numeric()
                            ->label('Nilai Akhir (NA)'),

                        Textarea::make('highest_achieved_description')
                            ->rows(3)
                            ->label('Deskripsi Capaian Tertinggi')
                            ->columnSpanFull(),

                        Textarea::make('lowest_achieved_description')
                            ->rows(3)
                            ->label('Deskripsi Perlu Bimbingan')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid(['default' => 1, 'md' => 1])
            ->columns([
                TextColumn::make('enrollment.student.name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->sortable(),

                TextColumn::make('final_score')
                    ->label('Nilai Akhir (NA)')
                    ->badge()
                    ->color(fn (string $state): string => (float)$state >= 70 ? 'success' : 'danger')
                    ->sortable(),

                TextColumn::make('highest_achieved_description')
                    ->label('Deskripsi Tertinggi')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('lowest_achieved_description')
                    ->label('Deskripsi Terendah')
                    ->limit(60)
                    ->wrap(),
            ])
            ->headerActions([
                Action::make('generateAll')
                    ->label('Kalkulasi Ulang Nilai Akhir')
                    ->icon('heroicon-o-calculator')
                    ->color('sky')
                    ->form([
                        Select::make('class_id')
                            ->label('Pilih Kelas')
                            ->options(function () {
                                $query = ClassRoom::query();
                                // Menggunakan helper dari HasRoleScope
                                static::applyRoleScope($query, 'teacher_id');
                                return $query->pluck('name', 'id');
                            })
                            ->reactive()
                            ->required(),

                        Select::make('subject_id')
                            ->label('Pilih Mata Pelajaran')
                            ->options(function (Get $get) {
                                $user = auth()->user();
                                $query = Subject::query();
                                $classId = $get('class_id');
                                $roles = static::getUserRoles($user);

                                if (!array_intersect($roles, ['admin', 'headmaster', 'super_admin']) && !($user->is_admin ?? false)) {
                                    $teacherId = $user->teacher?->id ?? $user->teacher_id;
                                    $query->whereHas('teacherSubjectClasses', function ($q) use ($teacherId, $classId) {
                                        $q->where('teacher_id', $teacherId);
                                        if ($classId) {
                                            $q->where('class_room_id', $classId);
                                        }
                                    });
                                } elseif ($classId) {
                                    $query->whereHas('teacherSubjectClasses', fn ($q) => $q->where('class_room_id', $classId));
                                }

                                return $query->pluck('name', 'id');
                            })
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        try {
                            GradeCalculationService::calculateBatchForClassroom($data['class_id'], $data['subject_id']);
                            Notification::make()
                                ->title('Proses Berhasil')
                                ->body('Seluruh Nilai Akhir dan Deskripsi CP berhasil diperbarui.')
                                ->success()
                                ->send();
                        } catch (\Throwable $th) {
                            Notification::make()
                                ->title('Gagal Kalkulasi')
                                ->body($th->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGradeFinalScores::route('/'),
            'edit' => Pages\EditGradeFinalScore::route('/{record}/edit'),
        ];
    }
}