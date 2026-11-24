<?php

namespace App\Filament\Resources\Administration;

use App\Filament\Concerns\HasModulePermissions;
use App\Filament\Resources\Administration\PrayerResource\Pages;
use App\Models\Prayer;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PrayerResource extends Resource
{
    use HasModulePermissions;

    protected static ?string $model = Prayer::class;

    protected static ?string $modelLabel = 'Oración o Novena';

    protected static ?string $pluralModelLabel = 'Oraciones y Novenas';

    protected static function getPermissionModule(): string
    {
        return 'prayers';
    }

    protected static ?string $navigationLabel = 'Oraciones y Novenas';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Básica')
                    ->schema([
                        Select::make('type')
                            ->label('Tipo de Registro')
                            ->options([
                                'oracion' => 'Oración',
                                'novena'  => 'Novena',
                            ])
                            ->required()
                            ->default('oracion')
                            ->live(),

                        TextInput::make('title')
                            ->label('Título / Nombre')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ej. Oración de Consagración o Novena al Espíritu Santo'),
                    ]),

                Section::make('Contenido de la Oración')
                    ->description('Pega aquí el texto completo de la oración y opcionalmente adjunta una imagen representativa.')
                    ->visible(fn(Get $get): bool => $get('type') === 'oracion')
                    ->schema([
                        RichEditor::make('content')
                            ->label('Texto de la Oración')
                            ->required(fn(Get $get): bool => $get('type') === 'oracion')
                            ->placeholder('Escribe o pega el texto de la oración aquí...')
                            ->columnSpanFull(),

                        FileUpload::make('image_path')
                            ->label('Imagen (Opcional)')
                            ->image()
                            ->disk('public')
                            ->directory('prayers')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ]),

                Section::make('Días y Contenido de la Novena')
                    ->description('Organiza la novena día por día con su texto correspondiente y una imagen opcional para cada día.')
                    ->visible(fn(Get $get): bool => $get('type') === 'novena')
                    ->schema([
                        FileUpload::make('image_path')
                            ->label('Imagen Principal / Portada de la Novena (Opcional)')
                            ->image()
                            ->disk('public')
                            ->directory('prayers/novenas')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->columnSpanFull(),

                        Repeater::make('days')
                            ->label('Días de la Novena')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Día / Título')
                                    ->placeholder('Ej. Día 1 - La Anunciación')
                                    ->required(),

                                RichEditor::make('content')
                                    ->label('Texto / Oración del Día')
                                    ->placeholder('Escribe o pega el texto correspondiente a este día...')
                                    ->required()
                                    ->columnSpanFull(),

                                FileUpload::make('image_path')
                                    ->label('Imagen del Día (Opcional)')
                                    ->image()
                                    ->disk('public')
                                    ->directory('prayers/novenas')
                                    ->visibility('public')
                                    ->maxSize(5120),
                            ])
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? 'Nuevo Día')
                            ->defaultItems(1)
                            ->addActionLabel('+ Agregar día a la novena')
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->searchPlaceholder('Buscar por título...')
            ->emptyStateHeading('No hay oraciones ni novenas registradas')
            ->emptyStateDescription('Crea una nueva oración o novena para comenzar.')
            ->emptyStateIcon(Heroicon::OutlinedBookOpen)
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'oracion' => 'Oración',
                        'novena'  => 'Novena',
                        default   => $state,
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'oracion' => 'amber',
                        'novena'  => 'info',
                        default   => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'oracion' => 'Oración',
                        'novena'  => 'Novena',
                    ]),

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
            'index'  => Pages\ListPrayers::route('/'),
            'create' => Pages\CreatePrayer::route('/create'),
            'edit'   => Pages\EditPrayer::route('/{record}/edit'),
        ];
    }
}
