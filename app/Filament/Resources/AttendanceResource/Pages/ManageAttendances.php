<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use App\Models\Event;
use App\Models\Assistant;
use App\Models\Attendance;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;

class ManageAttendances extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = AttendanceResource::class;

    protected string $view = 'filament.resources.attendance-resource.pages.manage-attendances';

    // State properties
    public ?int $selectedEventId = null;
    public ?Event $selectedEvent = null;
    
    public string $searchDocument = '';
    public ?Assistant $searchedAssistant = null;
    public bool $assistantExists = false;
    public bool $showRegisterForm = false;
    public bool $hasAttended = false;

    // Registration Form Fields
    public string $newAssistantDocument = '';
    public string $newAssistantName = '';
    public string $newAssistantEmail = '';
    public ?string $newAssistantPhone = '';

    public function getTitle(): string
    {
        return 'Control de Asistencia';
    }

    public function selectEvent(int $eventId): void
    {
        $event = Event::find($eventId);
        if ($event && in_array($event->status, ['published', 'not_published'])) {
            $this->selectedEventId = $eventId;
            $this->selectedEvent = $event;
            $this->resetSearch();
            $this->resetTable();
        } else {
            Notification::make()
                ->title('Evento inválido o no disponible para registro')
                ->danger()
                ->send();
        }
    }

    public function deselectEvent(): void
    {
        $this->selectedEventId = null;
        $this->selectedEvent = null;
        $this->resetSearch();
        $this->resetTable();
    }

    public function updatedSearchDocument($value): void
    {
        $value = trim($value);
        if (empty($value)) {
            $this->cancelSearch();
            return;
        }

        $assistant = Assistant::where('document', $value)->first();

        if ($assistant) {
            $this->searchedAssistant = $assistant;
            $this->assistantExists = true;
            $this->showRegisterForm = false;
            $this->hasAttended = Attendance::where('event_id', $this->selectedEventId)
                ->where('assistant_id', $assistant->id)
                ->exists();
        } else {
            $this->searchedAssistant = null;
            $this->assistantExists = false;
            $this->showRegisterForm = true;
            $this->hasAttended = false;
            $this->newAssistantDocument = $value;
            $this->newAssistantName = '';
            $this->newAssistantEmail = '';
            $this->newAssistantPhone = '';
        }
    }

    public function markAttendance(): void
    {
        if (!$this->selectedEventId || !$this->searchedAssistant) {
            return;
        }

        if ($this->hasAttended) {
            Notification::make()
                ->title('Este asistente ya registró su asistencia')
                ->warning()
                ->send();
            return;
        }

        Attendance::create([
            'event_id' => $this->selectedEventId,
            'assistant_id' => $this->searchedAssistant->id,
        ]);

        Notification::make()
            ->title('Asistencia registrada con éxito')
            ->success()
            ->send();

        $this->resetSearch();
    }

    public function registerAndMarkAttendance(): void
    {
        $this->validate([
            'newAssistantDocument' => 'required|string|max:255|unique:assistants,document',
            'newAssistantName' => 'required|string|max:255',
            'newAssistantEmail' => 'nullable|email|max:255|unique:assistants,email',
            'newAssistantPhone' => 'nullable|string|max:30',
        ], [
            'newAssistantDocument.unique' => 'Esta cédula ya está registrada.',
            'newAssistantEmail.unique' => 'Este correo electrónico ya está registrado.',
            'newAssistantName.required' => 'El nombre completo es obligatorio.',
        ]);

        $email = trim($this->newAssistantEmail);
        $email = $email === '' ? null : $email;

        $assistant = Assistant::create([
            'document' => $this->newAssistantDocument,
            'name' => $this->newAssistantName,
            'email' => $email,
            'phone' => $this->newAssistantPhone,
        ]);

        Attendance::create([
            'event_id' => $this->selectedEventId,
            'assistant_id' => $assistant->id,
        ]);

        Notification::make()
            ->title('Asistente registrado y asistencia marcada')
            ->success()
            ->send();

        $this->resetSearch();
    }

    public function cancelSearch(): void
    {
        $this->resetSearch();
    }

    private function resetSearch(): void
    {
        $this->searchDocument = '';
        $this->searchedAssistant = null;
        $this->assistantExists = false;
        $this->showRegisterForm = false;
        $this->hasAttended = false;
        
        $this->newAssistantDocument = '';
        $this->newAssistantName = '';
        $this->newAssistantEmail = '';
        $this->newAssistantPhone = '';
    }

    public function getEvents(): \Illuminate\Database\Eloquent\Collection
    {
        return Event::query()
            ->whereIn('status', ['published', 'not_published'])
            ->orderBy('start_datetime', 'desc')
            ->get();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->where('event_id', $this->selectedEventId)
                    ->with('assistant')
            )
            ->columns([
                TextColumn::make('assistant.document')
                    ->label('Cédula / Documento')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('assistant.name')
                    ->label('Nombre Completo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('assistant.email')
                    ->label('Correo Electrónico')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Hora de Registro')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('refresh')
                    ->label('Refrescar')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(fn () => null),
            ])
            ->emptyStateHeading('No hay asistentes registrados para este evento')
            ->emptyStateDescription('Digita la cédula de un asistente arriba para comenzar el registro de asistencia.');
    }
}
