<?php

namespace App\Livewire\Profile;

use App\Models\Assistant;
use App\Models\TeamMember;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo;

class CustomPersonalInfo extends PersonalInfo
{
    protected string $view = 'livewire.profile.custom-personal-info';

    public static $sort = 10;

    public ?int $assistantId = null;

    public bool $hasAssistantRecord = false;

    public function mount(): void
    {
        $this->user = filament('filament-breezy')->auth()->user();
        $this->userClass = get_class($this->user);
        $this->hasAvatars = filament('filament-breezy')->hasAvatars();

        if ($this->hasAvatars) {
            $this->only[] = 'avatar_url';
        }

        $assistant = null;

        // Cruzar usuario con asistente por correo (excepto la cuenta genérica admin)
        if ($this->user && ! $this->user->isSystemAdminAccount()) {
            $assistant = Assistant::query()
                ->whereRaw('LOWER(email) = ?', [strtolower((string) $this->user->email)])
                ->first();

            $this->assistantId = $assistant?->id;
            $this->hasAssistantRecord = $assistant !== null;
        }

        $formData = [
            'avatar_url' => $this->user->avatar_url,
            'name' => $assistant?->name ?: $this->user->name,
            'email' => $this->user->email,
            'occupation' => TeamMember::normalizeOccupation($this->user->occupation),
            'joined_since' => TeamMember::extractYear($this->user->joined_since),
            'testimony' => $this->user->testimony,
            'favorite_verse' => filled($this->user->favorite_verse) ? trim($this->user->favorite_verse, " \"'«»“”") : null,
        ];

        if ($assistant) {
            $formData['document'] = $assistant->document;
            $formData['phone'] = $assistant->phone;
            $formData['birth_date'] = $assistant->birth_date?->format('Y-m-d');
        }

        $this->form->fill($formData);
    }

    protected function getProfileFormSchema(): array
    {
        $assistant = $this->assistantId ? Assistant::find($this->assistantId) : null;

        $components = [
            Section::make('Información Básica')
                ->compact()
                ->schema([
                    FileUpload::make('avatar_url')
                        ->label('Foto de Perfil')
                        ->avatar()
                        ->image()
                        ->imageEditor()
                        ->maxSize(4096)
                        ->disk('public')
                        ->directory('avatars')
                        ->visibility('public')
                        ->alignCenter(),

                    TextInput::make('name')
                        ->label('Nombre Completo')
                        ->prefixIcon(Heroicon::OutlinedUser)
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Correo Electrónico')
                        ->prefixIcon(Heroicon::OutlinedEnvelope)
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('El correo electrónico no puede ser modificado desde tu perfil.'),
                ]),
        ];

        if ($this->hasAssistantRecord) {
            $components[] = Section::make('Información Personal de Asistente')
                ->description('Sincronizada automáticamente con tu registro de asistente.')
                ->compact()
                ->schema([
                    TextInput::make('document')
                        ->label('Documento de Identidad')
                        ->prefixIcon(Heroicon::OutlinedIdentification)
                        ->required()
                        ->maxLength(50)
                        ->unique(
                            table: Assistant::class,
                            column: 'document',
                            ignorable: $assistant
                        ),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('phone')
                                ->label('Teléfono / WhatsApp')
                                ->prefixIcon(Heroicon::OutlinedPhone)
                                ->tel()
                                ->maxLength(30)
                                ->placeholder('Ej. 3001234567'),

                            DatePicker::make('birth_date')
                                ->label('Fecha de Nacimiento')
                                ->prefixIcon(Heroicon::OutlinedCake)
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->maxDate(now())
                                ->placeholder('Selecciona tu fecha'),
                        ]),
                ]);
        }

        if (! $this->user?->isSystemAdminAccount()) {
            $components[] = Section::make('Mi Presentación e Historia en la Comunidad')
                ->description('Completa estos 4 pasos guiados. El sistema les dará un formato uniforme para que todas las tarjetas del Organigrama se vean armónicas cuando alguien dé clic sobre tu foto.')
                ->compact()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('occupation')
                                ->label('1. Profesión, Oficio o Vocación')
                                ->prefix('Soy')
                                ->maxLength(120)
                                ->placeholder('Ej. Médica Pediatra, Docente, Arquitecto...')
                                ->helperText('Escribe solo tu profesión u oficio (sin escribir «Yo soy»).'),

                            Select::make('joined_since')
                                ->label('2. ¿Desde qué año caminas en Resucitados?')
                                ->prefixIcon(Heroicon::OutlinedCalendarDays)
                                ->options(TeamMember::joinedYearOptions())
                                ->searchable()
                                ->placeholder('Selecciona el año')
                                ->helperText('Se mostrará como «En Resucitados desde [Año]».'),
                        ]),

                    Textarea::make('testimony')
                        ->label('3. Tu Historia Corta en la Comunidad (Responde en un párrafo breve)')
                        ->rows(4)
                        ->maxLength(450)
                        ->placeholder('Ej. Llegué a la comunidad invitada a una asamblea cuando atravesaba un momento de mucha incertidumbre. Aquí encontré una familia en la fe que me enseñó a orar y a descubrir que Dios tenía un propósito hermoso para mi vida y mi vocación.')
                        ->helperText('Guía sugerida (máx. 450 caracteres): ¿Cómo llegaste a la comunidad y cómo ha transformado Dios tu vida al servir aquí?'),

                    TextInput::make('favorite_verse')
                        ->label('4. Cita Bíblica o Frase que te inspira (Sin comillas)')
                        ->prefixIcon(Heroicon::OutlinedSparkles)
                        ->maxLength(220)
                        ->placeholder('Ej. Todo lo puedo en Cristo que me fortalece — Filipenses 4, 13')
                        ->helperText('El sistema agregará las comillas « » automáticamente con el mismo diseño para todos.'),
                ]);
        }

        return $components;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components($this->getProfileFormSchema())
            ->columns(1)
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();
        $assistant = $this->assistantId ? Assistant::find($this->assistantId) : null;

        DB::transaction(function () use ($state, $assistant): void {
            $this->user->update([
                'name' => $state['name'],
                'avatar_url' => $state['avatar_url'] ?? $this->user->avatar_url,
                'occupation' => TeamMember::normalizeOccupation($state['occupation'] ?? null),
                'joined_since' => TeamMember::extractYear($state['joined_since'] ?? null),
                'testimony' => TeamMember::normalizeTestimony($state['testimony'] ?? null),
                'favorite_verse' => isset($state['favorite_verse']) ? trim((string) $state['favorite_verse'], " \"'«»“”") ?: null : null,
            ]);

            if ($assistant) {
                $assistant->update([
                    'name' => $state['name'],
                    'document' => $state['document'],
                    'phone' => $state['phone'] ?? null,
                    'birth_date' => $state['birth_date'] ?? null,
                ]);
            }
        });

        $this->user->refresh();
        $assistant?->refresh();

        $formData = [
            'avatar_url' => $this->user->avatar_url,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'occupation' => $this->user->occupation,
            'joined_since' => $this->user->joined_since,
            'testimony' => $this->user->testimony,
            'favorite_verse' => $this->user->favorite_verse,
        ];

        if ($assistant) {
            $formData['document'] = $assistant->document;
            $formData['phone'] = $assistant->phone;
            $formData['birth_date'] = $assistant->birth_date?->format('Y-m-d');
        }

        $this->form->fill($formData);

        Notification::make()
            ->success()
            ->title('Información actualizada')
            ->body($assistant
                ? 'Se actualizaron tus datos de usuario y de asistente correctamente.'
                : 'Tu información de perfil se actualizó correctamente.')
            ->send();
    }
}
