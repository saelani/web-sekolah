<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolFacilityResource\Pages;
use App\Models\SchoolFacility;
use App\Traits\HasRoleScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SchoolFacilityResource extends Resource
{
    use HasRoleScope;

    protected static ?string $model = SchoolFacility::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Manajemen Web';

    protected static ?string $navigationLabel = 'Fasilitas Sekolah';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Fasilitas')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Fasilitas')
                            ->required(),

                        FileUpload::make('image_path')
                            ->label('Foto Fasilitas')
                            ->image()
                            ->directory('facilities'),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')->label('Foto'),
                TextColumn::make('name')->label('Nama Fasilitas')->searchable()->sortable(),
                TextColumn::make('description')->label('Deskripsi')->limit(50),
                TextColumn::make('created_at')->label('Dibuat Pada')->dateTime('d M Y')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolFacilities::route('/'),
            'create' => Pages\CreateSchoolFacility::route('/create'),
            'edit' => Pages\EditSchoolFacility::route('/{record}/edit'),
        ];
    }
}
