<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolAchievementResource\Pages;
use App\Models\SchoolAchievement;
use App\Traits\HasRoleScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchoolAchievementResource extends Resource
{
    use HasRoleScope;

    protected static ?string $model = SchoolAchievement::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Manajemen Web';

    protected static ?string $navigationLabel = 'Prestasi Sekolah';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Prestasi')
                    ->schema([
                        TextInput::make('title')
                            ->label('Nama Lomba / Prestasi')
                            ->required(),

                        TextInput::make('winner_name')
                            ->label('Nama Pemenang / Juara')
                            ->required(),

                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'Akademik' => 'Akademik',
                                'Non-Akademik' => 'Non-Akademik',
                            ])
                            ->required(),

                        Select::make('level')
                            ->label('Tingkat')
                            ->options([
                                'Kecamatan' => 'Kecamatan',
                                'Kota/Kab' => 'Kota/Kab',
                                'Provinsi' => 'Provinsi',
                                'Nasional' => 'Nasional',
                                'Internasional' => 'Internasional',
                            ])
                            ->required(),

                        DatePicker::make('achievement_date')
                            ->label('Tanggal Prestasi')
                            ->required(),

                        FileUpload::make('image_path')
                            ->label('Foto Sertifikat / Kegiatan')
                            ->image()
                            ->directory('achievements'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->label('Foto'),
                TextColumn::make('title')->label('Prestasi')->searchable()->sortable(),
                TextColumn::make('winner_name')->label('Pemenang')->searchable(),
                TextColumn::make('category')->label('Kategori')->badge(),
                TextColumn::make('level')->label('Tingkat')->sortable(),
                TextColumn::make('achievement_date')->label('Tanggal')->date('d M Y')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolAchievements::route('/'),
            'create' => Pages\CreateSchoolAchievement::route('/create'),
            'edit' => Pages\EditSchoolAchievement::route('/{record}/edit'),
        ];
    }
}
