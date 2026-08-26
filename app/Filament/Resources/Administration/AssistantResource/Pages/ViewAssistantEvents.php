<?php

namespace App\Filament\Resources\Administration\AssistantResource\Pages;

use App\Filament\Resources\Administration\AssistantResource;
use App\Models\Assistant;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;

class ViewAssistantEvents extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = AssistantResource::class;

    protected string $view = 'filament.resources.assistant-resource.pages.view-assistant-events';

    public Assistant $record;

    public function mount(Assistant $record): void
    {
        $this->record = $record;
    }

    public function getTitle(): string
    {
        return 'Eventos Asistidos';
    }

    public function getHeading(): string
    {
        return 'Eventos de: ' . $this->record->name;
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
                $this->record->events()->getQuery()
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Título del Evento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('eventType.name')
                    ->label('Tipo de Evento')
                    ->sortable(),

                TextColumn::make('modality')
                    ->label('Modalidad')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_person' => 'warning',
                        'virtual' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_person' => 'Presencial',
                        'virtual' => 'Virtual',
                    }),

                TextColumn::make('start_datetime')
                    ->label('Fecha de Inicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('start_datetime', 'desc');
    }
}
