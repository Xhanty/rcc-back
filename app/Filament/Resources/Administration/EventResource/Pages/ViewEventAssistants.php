<?php

namespace App\Filament\Resources\Administration\EventResource\Pages;

use App\Filament\Resources\Administration\EventResource;
use App\Models\Event;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;

class ViewEventAssistants extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = EventResource::class;

    protected string $view = 'filament.resources.event-resource.pages.view-event-assistants';

    public Event $record;

    public function mount(Event $record): void
    {
        $this->record = $record;
    }

    public function getTitle(): string
    {
        return 'Asistentes';
    }

    public function getHeading(): string
    {
        return 'Asistentes del Evento: ' . $this->record->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Regresar')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(static::getResource()::getUrl('index')),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->record->assistants()->getQuery()
            )
            ->columns([
                TextColumn::make('document')
                    ->label('Cédula / Documento')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('name')
                    ->label('Nombre Completo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->toggleable(),
            ])
            ->defaultSort('name', 'asc');
    }
}
