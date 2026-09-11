<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SubjectResource\Pages;
use App\Models\Subject;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Traits\HasRoleScope;
use App\Traits\HasAdminOrHeadmasterAccess;

class SubjectResource extends Resource
{
    use HasRoleScope; // 2. Gunakan Trait di sini
    use HasAdminOrHeadmasterAccess;
    protected static ?string $model = Subject::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $navigationLabel = 'Mata Pelajaran';
    protected static ?int $navigationSort = 1;

    // Otorisasi Hak Aksi (Create, Edit, Delete) khusus Admin
    public static function canCreate(): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'headmaster']);
    }

    public static function canEdit($record): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'headmaster']);
    }

    public static function canDelete($record): bool
    {
        return in_array(auth()->user()?->role, ['admin', 'headmaster']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Mata Pelajaran')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Mapel')
                            ->placeholder('MIS: MTK, BIN, IPA')
                            ->required()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Mata Pelajaran')
                            ->placeholder('Contoh: Matematika')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('group')
                            ->label('Kelompok Mapel')
                            ->options([
                                'A' => 'Kelompok A (Umum / Wajib)',
                                'B' => 'Kelompok B (Seni & Olahraga)',
                                'mulok' => 'Muatan Lokal (Mulok)',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('order_number')
                            ->label('Urutan di Rapor')
                            ->numeric()
                            ->default(1)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('No.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('group')
                    ->label('Kelompok')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'A' => 'primary',
                        'B' => 'success',
                        'mulok' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('order_number', 'asc')
            ->actions([
                Tables\Actions\EditAction::make()->color('warning'),
                Tables\Actions\DeleteAction::make()->color('danger'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSubjects::route('/'),
            'create' => Pages\CreateSubject::route('/create'),
            'edit'   => Pages\EditSubject::route('/{record}/edit'),
        ];
    }
}