<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Concerns\HasModulePermissions;
use App\Filament\Resources\Administration\SocialMediaItemResource\Pages;
use App\Models\SocialMediaItem;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class SocialMediaItemResource extends Resource
{
    use HasModulePermissions;

    protected static ?string $model = SocialMediaItem::class;

    protected static ?string $modelLabel = 'Red o Medio Digital';

    protected static ?string $pluralModelLabel = 'Redes y Medios Digitales';

    protected static function getPermissionModule(): string
    {
        return 'social_media';
    }

    protected static ?string $navigationLabel = 'Redes y Medios';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 12;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Red Social y Enlace General del Perfil')
                    ->description('Configura un único registro por red social. El enlace general se usa para el icono del Footer y el botón principal («Ver en Instagram», «Abrir en Spotify», etc.).')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('platform')
                                    ->label('Plataforma / Red Social')
                                    ->options(SocialMediaItem::platformOptions())
                                    ->required()
                                    ->default(SocialMediaItem::PLATFORM_INSTAGRAM)
                                    ->live()
                                    ->unique(
                                        table: SocialMediaItem::class,
                                        column: 'platform',
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at')
                                    )
                                    ->validationMessages([
                                        'unique' => 'Ya existe un registro para esta red social. Edita el existente para agregar más publicaciones o videos.',
                                    ]),

                                TextInput::make('url')
                                    ->label(fn (Get $get): string => match ($get('platform')) {
                                        SocialMediaItem::PLATFORM_INSTAGRAM => 'Enlace del Perfil de Instagram (General)',
                                        SocialMediaItem::PLATFORM_FACEBOOK => 'Enlace de la Página de Facebook',
                                        SocialMediaItem::PLATFORM_SPOTIFY => 'Enlace General de Spotify (Perfil o Playlist principal)',
                                        SocialMediaItem::PLATFORM_YOUTUBE => 'Enlace General de YouTube (Opcional si no hay canal)',
                                        default => 'Enlace General (URL)',
                                    })
                                    ->prefixIcon(Heroicon::OutlinedLink)
                                    ->url()
                                    ->maxLength(2048)
                                    ->required(fn (Get $get): bool => (bool) $get('show_in_footer') || $get('platform') === SocialMediaItem::PLATFORM_FACEBOOK)
                                    ->placeholder(fn (Get $get): string => match ($get('platform')) {
                                        SocialMediaItem::PLATFORM_INSTAGRAM => 'https://www.instagram.com/resucitadosrcc/',
                                        SocialMediaItem::PLATFORM_FACEBOOK => 'https://www.facebook.com/resucitadosrcc',
                                        SocialMediaItem::PLATFORM_SPOTIFY => 'https://open.spotify.com/playlist/...',
                                        SocialMediaItem::PLATFORM_YOUTUBE => 'Opcional (dejar vacío si solo agregas videos abajo)',
                                        default => 'https://...',
                                    })
                                    ->helperText('Este link general lleva a la red social (Footer y botón principal), no a una publicación suelta.'),
                            ]),
                    ]),

                Section::make('¿Dónde se mostrará esta Red Social?')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('show_in_community')
                                    ->label('Mostrar en Vida Comunitaria (Medios Digitales)')
                                    ->default(true)
                                    ->live()
                                    ->visible(fn (Get $get): bool => in_array($get('platform'), SocialMediaItem::embeddablePlatforms(), true))
                                    ->helperText('Muestra las publicaciones, videos o playlists agregados abajo juntos en la sección de Medios Digitales.'),

                                Toggle::make('show_in_footer')
                                    ->label('Mostrar en el Footer (Icono de Red Social)')
                                    ->default(true)
                                    ->live()
                                    ->helperText('Muestra un único icono en el pie de página que lleva al enlace general del perfil.'),

                                TextInput::make('sort_order')
                                    ->label('Orden de Visualización')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->default(fn (): int => max(1, ((int) SocialMediaItem::query()->max('sort_order')) + 1))
                                    ->minValue(1)
                                    ->unique(
                                        table: SocialMediaItem::class,
                                        column: 'sort_order',
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at')
                                    )
                                    ->validationMessages([
                                        'unique' => 'Ya existe otra red social con este número de orden.',
                                    ]),

                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                    ]),

                Section::make(fn (Get $get): string => match ($get('platform')) {
                    SocialMediaItem::PLATFORM_INSTAGRAM => 'Publicaciones / Reels de Instagram a mostrar',
                    SocialMediaItem::PLATFORM_YOUTUBE => 'Videos Recomendados de YouTube a mostrar',
                    SocialMediaItem::PLATFORM_SPOTIFY => 'Playlists / Álbumes de Spotify a mostrar',
                    default => 'Publicaciones Específicas',
                })
                    ->description('Agrega aquí uno o varios links de publicaciones específicas (fotos/carruseles/reels de Instagram, videos recomendados de YouTube o playlists de Spotify) para que salgan juntos en esta red.')
                    ->visible(fn (Get $get): bool => in_array($get('platform'), SocialMediaItem::embeddablePlatforms(), true) && (bool) $get('show_in_community'))
                    ->schema([
                        Repeater::make('posts')
                            ->label(fn (Get $get): string => match ($get('platform')) {
                                SocialMediaItem::PLATFORM_INSTAGRAM => 'Publicaciones o Reels',
                                SocialMediaItem::PLATFORM_YOUTUBE => 'Videos Recomendados',
                                SocialMediaItem::PLATFORM_SPOTIFY => 'Playlists',
                                default => 'Publicaciones',
                            })
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('url')
                                            ->label('Link de la Publicación / Video / Playlist')
                                            ->prefixIcon(Heroicon::OutlinedLink)
                                            ->required()
                                            ->url()
                                            ->maxLength(2048)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                                $platform = (string) ($get('../../platform') ?? '');
                                                if (blank($state) || blank($platform)) {
                                                    return;
                                                }

                                                $meta = SocialMediaItem::extractMetadataFromUrl($platform, $state);
                                                if (blank($get('title')) && filled($meta['title'])) {
                                                    $set('title', $meta['title']);
                                                }
                                            })
                                            ->placeholder(fn (Get $get): string => match ($get('../../platform')) {
                                                SocialMediaItem::PLATFORM_INSTAGRAM => 'https://www.instagram.com/p/... o https://www.instagram.com/reel/...',
                                                SocialMediaItem::PLATFORM_YOUTUBE => 'https://www.youtube.com/watch?v=...',
                                                SocialMediaItem::PLATFORM_SPOTIFY => 'https://open.spotify.com/playlist/...',
                                                default => 'https://...',
                                            }),

                                        TextInput::make('title')
                                            ->label('Título (Opcional · se autocompleta en YouTube/Spotify)')
                                            ->maxLength(255)
                                            ->placeholder('Ej. Resucitados 2027 o Enseñanza de la semana'),
                                    ]),

                                Textarea::make('description')
                                    ->label('Descripción corta (Opcional)')
                                    ->rows(2)
                                    ->maxLength(500)
                                    ->placeholder('Breve texto descriptivo para acompañar esta publicación o video...'),
                            ])
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => filled($state['title'] ?? null) ? $state['title'] : ($state['url'] ?? 'Nueva publicación'))
                            ->addActionLabel(fn (Get $get): string => match ($get('platform')) {
                                SocialMediaItem::PLATFORM_INSTAGRAM => '+ Agregar otra publicación o Reel de Instagram',
                                SocialMediaItem::PLATFORM_YOUTUBE => '+ Agregar otro video recomendado de YouTube',
                                SocialMediaItem::PLATFORM_SPOTIFY => '+ Agregar otra playlist de Spotify',
                                default => '+ Agregar publicación',
                            })
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->reorderable(fn (): ?string => (Auth::user()?->hasPermission('social_media', 'edit') ?? false) ? 'sort_order' : null)
            ->striped()
            ->searchPlaceholder('Buscar por título o plataforma...')
            ->emptyStateHeading('No hay redes ni medios digitales registrados')
            ->emptyStateDescription('Configura Spotify, YouTube, Instagram o Facebook.')
            ->emptyStateIcon(Heroicon::OutlinedGlobeAlt)
            ->columns([
                ImageColumn::make('thumbnail_url')
                    ->label('Miniatura')
                    ->height(44)
                    ->defaultImageUrl(asset('icon.png'))
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),

                TextColumn::make('platform')
                    ->label('Red Social')
                    ->formatStateUsing(fn (string $state): string => SocialMediaItem::platformOptions()[$state] ?? ucfirst($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        SocialMediaItem::PLATFORM_SPOTIFY => 'success',
                        SocialMediaItem::PLATFORM_YOUTUBE => 'danger',
                        SocialMediaItem::PLATFORM_INSTAGRAM => 'warning',
                        SocialMediaItem::PLATFORM_FACEBOOK => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Contenido Principal')
                    ->default(fn (SocialMediaItem $record): string => $record->title ?: (SocialMediaItem::platformOptions()[$record->platform] ?? ucfirst($record->platform)))
                    ->searchable(['title', 'platform'])
                    ->weight('bold')
                    ->limit(50)
                    ->description(function (SocialMediaItem $record): string {
                        $count = is_array($record->posts) ? count($record->posts) : 0;

                        return $count > 0
                            ? "✨ {$count} " . ($count === 1 ? 'publicación configurada' : 'publicaciones configuradas')
                            : 'Perfil general configurado';
                    }),

                ToggleColumn::make('show_in_community')
                    ->label('Medios Digitales')
                    ->disabled(fn (): bool => ! (Auth::user()?->hasPermission('social_media', 'edit') ?? false)),

                ToggleColumn::make('show_in_footer')
                    ->label('Footer')
                    ->disabled(fn (): bool => ! (Auth::user()?->hasPermission('social_media', 'edit') ?? false)),

                ToggleColumn::make('is_active')
                    ->label('Activo')
                    ->disabled(fn (): bool => ! (Auth::user()?->hasPermission('social_media', 'edit') ?? false)),

                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('platform')
                    ->label('Red Social')
                    ->options(SocialMediaItem::platformOptions()),

                TernaryFilter::make('show_in_community')
                    ->label('En Medios Digitales'),

                TernaryFilter::make('show_in_footer')
                    ->label('En Footer'),

                TernaryFilter::make('is_active')
                    ->label('Estado'),

                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSocialMediaItems::route('/'),
            'create' => Pages\CreateSocialMediaItem::route('/create'),
            'edit'   => Pages\EditSocialMediaItem::route('/{record}/edit'),
        ];
    }
}
