<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Traits\HasRoleScope;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    use HasRoleScope;

    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Manajemen Web';

    protected static ?string $navigationLabel = 'Berita & Artikel';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Berita')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Berita')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->required()
                            ->unique(Post::class, 'slug', ignoreRecord: true),

                        Select::make('category_id')
                            ->relationship('category', 'name') // Memanggil method category()
                            ->label('Kategori')
                            ->searchable()
                            ->preload()
                            ->required(),

                        // Menyiapkan user_id secara otomatis dari user login
                        Hidden::make('user_id')
                            ->default(fn () => auth()->id())
                            ->required(),

                        Textarea::make('excerpt')
                            ->label('Kutipan Singkat')
                            ->rows(2)
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Isi Berita')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Publikasi & Media')
                    ->schema([
                        FileUpload::make('thumbnail_path')
                            ->label('Gambar Sampul')
                            ->image()
                            ->directory('posts'),

                        Toggle::make('is_published')
                            ->label('Publikasikan')
                            ->default(true),

                        DateTimePicker::make('published_at')
                            ->label('Tanggal Terbit')
                            ->default(now()),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_path')
                    ->label('Gambar'),

                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name') // Memanggil relasi category()
                    ->label('Kategori')
                    ->sortable(),

                TextColumn::make('author.name') // Menggunakan author.name sesuai method author() di model
                    ->label('Penulis')
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Terbit')
                    ->boolean(),

                TextColumn::make('views_count')
                    ->label('Dilihat')
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label('Tanggal Terbit')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
