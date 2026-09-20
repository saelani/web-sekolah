<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolProfileResource\Pages;
use App\Models\SchoolProfile;
use App\Traits\HasAdminOrHeadmasterAccess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SchoolProfileResource extends Resource
{
    use HasAdminOrHeadmasterAccess;

    protected static ?string $model = SchoolProfile::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Manajemen Sekolah';

    protected static ?string $navigationLabel = 'Profil Sekolah';

    protected static ?string $modelLabel = 'Profil Sekolah';

    protected static ?string $pluralModelLabel = 'Profil Sekolah';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: Identitas Utama Sekolah
                Forms\Components\Section::make('Identitas Utama Sekolah')
                    ->description('Data pokok legalitas dan akreditasi sekolah.')
                    ->schema([
                        Forms\Components\TextInput::make('npsn')
                            ->label('NPSN')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Contoh: 10203040'),

                        Forms\Components\TextInput::make('school_name')
                            ->label('Nama Sekolah')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: SD Negeri 1 Cerdas'),

                        Forms\Components\TextInput::make('headmaster_name')
                            ->label('Nama Kepala Sekolah')
                            ->maxLength(255)
                            ->placeholder('Lengkap dengan gelar'),

                        Forms\Components\Select::make('accreditation')
                            ->label('Akreditasi')
                            ->options([
                                'A' => 'Akreditasi A',
                                'B' => 'Akreditasi B',
                                'C' => 'Akreditasi C',
                            ])
                            ->default('A')
                            ->required()
                            ->native(false),
                    ])->columns(['default' => 1, 'md' => 2]),

                // Section 2: Kontak & Alamat
                Forms\Components\Section::make('Kontak & Alamat Sekolah')
                    ->description('Informasi komunikasi dan lokasi fisik sekolah.')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(30)
                            ->placeholder('Contoh: 021-1234567'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email Resmi')
                            ->email()
                            ->maxLength(100)
                            ->placeholder('sdn1cerdas@kemdikbud.go.id'),

                        Forms\Components\TextInput::make('website')
                            ->label('Situs Web')
                            ->url()
                            ->maxLength(100)
                            ->placeholder('https://sdn1cerdas.sch.id'),

                        Forms\Components\Textarea::make('maps_embed')
                            ->label('Embed Google Maps (Iframe/URL)')
                            ->rows(2)
                            ->placeholder('Tempelkan kode iframe dari Google Maps di sini'),
                    ])->columns(['default' => 1, 'md' => 3]),

                // Section 3: Visi, Misi & Media
                Forms\Components\Section::make('Visi, Misi & Logo')
                    ->description('Visi-misi sekolah dan identitas visual.')
                    ->schema([
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo Sekolah')
                            ->image()
                            ->disk('public')
                            ->directory('school-logos')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->imageEditor()
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('vision')
                            ->label('Visi Sekolah')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList'])
                            ->columnSpan(['default' => 1, 'md' => 1]),

                        Forms\Components\RichEditor::make('mission')
                            ->label('Misi Sekolah')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList'])
                            ->columnSpan(['default' => 1, 'md' => 1]),
                    ])->columns(['default' => 1, 'md' => 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-logo.png')),

                Tables\Columns\TextColumn::make('npsn')
                    ->label('NPSN')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('info'),

                Tables\Columns\TextColumn::make('school_name')
                    ->label('Nama Sekolah')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('headmaster_name')
                    ->label('Kepala Sekolah')
                    ->default('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('accreditation')
                    ->label('Akreditasi')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon')
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->default('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('accreditation')
                    ->label('Akreditasi')
                    ->options([
                        'A' => 'Akreditasi A',
                        'B' => 'Akreditasi B',
                        'C' => 'Akreditasi C',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->color('warning'),
                Tables\Actions\DeleteAction::make()
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolProfiles::route('/'),
            'create' => Pages\CreateSchoolProfile::route('/create'),
            'edit' => Pages\EditSchoolProfile::route('/{record}/edit'),
        ];
    }
}
