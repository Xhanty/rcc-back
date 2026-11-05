<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Resources\Administration\SongResource\Pages;
use App\Models\Song;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SongResource extends Resource
{
    protected static ?string $model = Song::class;

    protected static ?string $modelLabel = 'Canción';

    protected static ?string $pluralModelLabel = 'Canciones';

    public static function canViewAny(): bool
    {
        return Auth::user()?->hasModuleAccess('songs') ?? false;
    }

    protected static ?string $navigationLabel = 'Canciones';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 7;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMusicalNote;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la Canción')
                    ->schema([
                        TextInput::make('title')
                            ->label('Título de la Canción')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej. Dios Incomparable'),

                        TextInput::make('author')
                            ->label('Compositor o Autor')
                            ->maxLength(255)
                            ->placeholder('Ej. Generación 12 / Marcos Witt'),
                    ]),

                Section::make('Letra / Texto de la Canción')
                    ->description('Pega la letra completa, estrofas, coros o acordes de la canción.')
                    ->schema([
                        Textarea::make('lyrics')
                            ->label('Texto / Letra')
                            ->rows(12)
                            ->placeholder("Pega aquí la letra de la canción...\n\n[Estrofa 1]\n...\n\n[Coro]\n...")
                            ->columnSpanFull(),
                    ]),

                Section::make('Enlaces y Redes Sociales')
                    ->description('Guarda los enlaces opcionales de YouTube, Spotify o Instagram para escuchar o ver la canción.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('youtube_url')
                                    ->label('Enlace de YouTube')
                                    ->prefixIcon(Heroicon::OutlinedVideoCamera)
                                    ->url()
                                    ->maxLength(2048)
                                    ->placeholder('https://youtube.com/watch?v=...'),

                                TextInput::make('spotify_url')
                                    ->label('Enlace de Spotify')
                                    ->prefixIcon(Heroicon::OutlinedMusicalNote)
                                    ->url()
                                    ->maxLength(2048)
                                    ->placeholder('https://open.spotify.com/track/...'),

                                TextInput::make('instagram_url')
                                    ->label('Enlace de Instagram')
                                    ->prefixIcon(Heroicon::OutlinedCamera)
                                    ->url()
                                    ->maxLength(2048)
                                    ->placeholder('https://instagram.com/reel/...'),
                            ]),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->searchPlaceholder('Buscar por título o autor...')
            ->emptyStateHeading('No hay canciones registradas')
            ->emptyStateDescription('Crea una nueva canción para enriquecer los eventos.')
            ->emptyStateIcon(Heroicon::OutlinedMusicalNote)
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('author')
                    ->label('Autor / Compositor')
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('youtube_url')
                    ->label('YouTube')
                    ->icon(Heroicon::OutlinedVideoCamera)
                    ->formatStateUsing(fn(?string $state): string => filled($state) ? 'Ver video' : '—')
                    ->url(fn(?string $state): ?string => filled($state) ? $state : null, shouldOpenInNewTab: true)
                    ->color(fn(?string $state): string => filled($state) ? 'danger' : 'gray'),

                TextColumn::make('spotify_url')
                    ->label('Spotify')
                    ->icon(Heroicon::OutlinedMusicalNote)
                    ->formatStateUsing(fn(?string $state): string => filled($state) ? 'Escuchar' : '—')
                    ->url(fn(?string $state): ?string => filled($state) ? $state : null, shouldOpenInNewTab: true)
                    ->color(fn(?string $state): string => filled($state) ? 'success' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index'  => Pages\ListSongs::route('/'),
            'create' => Pages\CreateSong::route('/create'),
            'edit'   => Pages\EditSong::route('/{record}/edit'),
        ];
    }
}
