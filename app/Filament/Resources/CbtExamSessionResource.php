<?php
namespace App\Filament\Resources;

use App\Filament\Resources\CbtExamSessionResource\Pages;
use App\Models\CbtExamSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CbtExamSessionResource extends Resource
{
    protected static ?string $model = CbtExamSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Manajemen CBT & Nilai';

    protected static ?string $navigationLabel = 'Hasil & Sesi Ujian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('cbt_exam_id')
                    ->relationship('exam', 'title')
                    ->disabled(),

                Forms\Components\Select::make('student_id')
                    ->relationship('student', 'name')
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->options([
                        'ongoing' => 'Sedang Mengerjakan',
                        'submitted' => 'Selesai',
                        'blocked' => 'Diblokir',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('total_score')
                    ->label('Total Nilai')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exam.title')
                    ->label('Ujian')
                    ->searchable(),

                Tables\Columns\TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Mulai')
                    ->dateTime('d/m/Y H:i'),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Selesai')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Belum Selesai'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ongoing' => 'warning',
                        'submitted' => 'success',
                        'blocked' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_score')
                    ->label('Skor Akhir')
                    ->sortable()
                    ->weight('bold')
                    ->placeholder('0.00'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('cbt_exam_id')
                    ->label('Filter Ujian')
                    ->relationship('exam', 'title'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCbtExamSessions::route('/'),
        ];
    }
}