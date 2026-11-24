<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Concerns\HasModulePermissions;
use App\Filament\Resources\Administration\BannerResource\Pages;
use App\Models\Banner;
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
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
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

class BannerResource extends Resource
{
    use HasModulePermissions;

    protected static ?string $model = Banner::class;

    protected static ?string $modelLabel = 'Banner';

    protected static ?string $pluralModelLabel = 'Banners';

    protected static function getPermissionModule(): string
    {
        return 'banners';
    }

    protected static ?string $navigationLabel = 'Banners';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del Banner')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('section')
                                    ->label('Sección')
                                    ->options(Banner::sectionOptions())
                                    ->required()
                                    ->default(Banner::SECTION_HOME)
                                    ->live()
                                    ->afterStateUpdated(function (string $operation, ?string $state, Set $set): void {
                                        if ($operation === 'create' && filled($state)) {
                                            $next = ((int) Banner::query()->where('section', $state)->max('sort_order')) + 1;
                                            $set('sort_order', max(1, $next));
                                        }
                                    })
                                    ->helperText('Elige en qué lugar se mostrarán estas imágenes (puedes agregar varias imágenes para la misma sección).'),

                                TextInput::make('title')
                                    ->label('Título / Nombre')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ej. Banner Principal Inicio'),
                            ]),

                        Textarea::make('description')
                            ->label('Descripción / Texto Alternativo (Opcional)')
                            ->rows(2)
                            ->maxLength(500)
                            ->placeholder('Breve descripción del banner o texto alternativo para accesibilidad...')
                            ->columnSpanFull(),

                        TextInput::make('link_url')
                            ->label('Enlace de Destino (Opcional)')
                            ->prefixIcon(Heroicon::OutlinedLink)
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://...')
                            ->visible(fn (Get $get): bool => $get('section') !== Banner::SECTION_ADMIN_LOGIN)
                            ->helperText('Si se agrega un enlace, el usuario podrá hacer clic en el banner para ir a esa URL.')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('sort_order')
                                    ->label('Orden de Visualización')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->default(fn (Get $get): int => max(1, ((int) Banner::query()->where('section', $get('section') ?? Banner::SECTION_HOME)->max('sort_order')) + 1))
                                    ->minValue(1)
                                    ->unique(
                                        table: Banner::class,
                                        column: 'sort_order',
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule, Get $get) => $rule
                                            ->where('section', $get('section') ?? Banner::SECTION_HOME)
                                            ->whereNull('deleted_at')
                                    )
                                    ->validationMessages([
                                        'unique' => 'Ya existe un banner con este número de orden en la misma sección.',
                                    ])
                                    ->helperText('Número único por sección (1 aparece primero).'),

                                Toggle::make('is_active')
                                    ->label('Activo')
                                    ->default(true)
                                    ->inline(false)
                                    ->helperText('Solo los banners activos se muestran públicamente o en el inicio de sesión.'),
                            ]),
                    ]),

                Section::make('Archivos Multimedia (Imágenes o Video)')
                    ->description(fn (Get $get): string => $get('section') === Banner::SECTION_ABOUT
                        ? 'Para la sección Nosotros puedes subir un video corto en MP4/WebM (recomendado de 5 a 15 segundos, máx. 15 MB; en la web se reproducirá en bucle y sin sonido) o imágenes.'
                        : 'Sube una o varias imágenes en alta resolución (JPG, PNG o WebP, máximo 5 MB por archivo).')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label(fn (Get $get): string => $get('section') === Banner::SECTION_ABOUT ? 'Video o Imágenes' : 'Imágenes')
                            ->multiple()
                            ->reorderable()
                            ->appendFiles()
                            ->panelLayout('grid')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                                'image/gif',
                                'video/mp4',
                                'video/webm',
                                'video/quicktime',
                            ])
                            ->required()
                            ->maxSize(20480)
                            ->disk('public')
                            ->visibility('public')
                            ->directory('banners')
                            ->helperText(fn (Get $get): string => $get('section') === Banner::SECTION_ABOUT
                                ? 'Acepta video corto (.mp4, .webm, máx. 20 MB) o imágenes. El frontend reproducirá el video automáticamente sin música (muted).'
                                : 'Puedes subir una o varias imágenes (o video corto) para esta sección y arrastrarlas para definir su orden.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->reorderable(fn (): ?string => (Auth::user()?->hasPermission('banners', 'edit') ?? false) ? 'sort_order' : null)
            ->striped()
            ->searchPlaceholder('Buscar por título...')
            ->emptyStateHeading('No hay banners registrados')
            ->emptyStateDescription('Sube tu primer banner para el inicio del frontend o el login del panel administrativo.')
            ->emptyStateIcon(Heroicon::OutlinedPhoto)
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Vista Previa')
                    ->disk('public')
                    ->height(48)
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->getStateUsing(function (Banner $record): array {
                        $imagePaths = array_values(array_filter(
                            $record->getImagePaths(),
                            fn (string $path): bool => ! Banner::isVideoPath($path)
                        ));

                        return ! empty($imagePaths) ? $imagePaths : [asset('icon.png')];
                    })
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(function (Banner $record): ?string {
                        $hasVideo = collect($record->getImagePaths())->contains(fn (string $p): bool => Banner::isVideoPath($p));

                        return $hasVideo ? '🎬 Incluye video (sin sonido)' : null;
                    }),

                TextColumn::make('section')
                    ->label('Sección')
                    ->formatStateUsing(fn (string $state): string => Banner::sectionOptions()[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Banner::SECTION_HOME => 'success',
                        Banner::SECTION_ABOUT => 'info',
                        Banner::SECTION_ADMIN_LOGIN => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Activo')
                    ->disabled(fn (): bool => ! (Auth::user()?->hasPermission('banners', 'edit') ?? false)),

                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('section')
                    ->label('Sección')
                    ->options(Banner::sectionOptions()),

                TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->trueLabel('Solo activos')
                    ->falseLabel('Solo inactivos'),

                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
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
            'index'  => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit'   => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
