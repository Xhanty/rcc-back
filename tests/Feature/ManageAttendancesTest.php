<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Assistant;
use App\Models\Attendance;
use Livewire\Livewire;
use App\Filament\Resources\AttendanceResource\Pages\ManageAttendances;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ManageAttendancesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'is_super_admin' => true,
        ]);

        $eventType = EventType::create([
            'name' => 'Asamblea',
            'is_active' => true,
        ]);

        $this->event = Event::create([
            'event_type_id' => $eventType->id,
            'title' => 'Asamblea Comunitaria',
            'slug' => 'asamblea-comunitaria',
            'start_datetime' => now()->addDay(),
            'status' => 'published',
            'modality' => 'in_person',
        ]);
    }

    public function test_can_search_and_find_assistant_by_numeric_document(): void
    {
        $assistant = Assistant::create([
            'document' => '10203040',
            'name' => 'Juan Pérez',
            'birth_date' => '1995-05-10',
        ]);

        $this->actingAs($this->user);

        Livewire::test(ManageAttendances::class)
            ->call('selectEvent', $this->event->id)
            ->set('searchDocument', '10203040')
            ->assertSet('assistantExists', true)
            ->assertSet('isSearchingByName', false)
            ->assertSet('searchedAssistant.id', $assistant->id);
    }

    public function test_numeric_search_with_unregistered_document_shows_registration_form(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ManageAttendances::class)
            ->call('selectEvent', $this->event->id)
            ->set('searchDocument', '99887766')
            ->assertSet('assistantExists', false)
            ->assertSet('isSearchingByName', false)
            ->assertSet('showRegisterForm', true)
            ->assertSet('newAssistantDocument', '99887766');
    }

    public function test_can_search_by_name_and_select_assistant_from_results(): void
    {
        $assistant = Assistant::create([
            'document' => '12345678',
            'name' => 'Sebastián González',
            'birth_date' => '1998-03-15',
        ]);

        $this->actingAs($this->user);

        Livewire::test(ManageAttendances::class)
            ->call('selectEvent', $this->event->id)
            ->set('searchDocument', 'sebas')
            ->assertSet('isSearchingByName', true)
            ->assertSet('assistantExists', false)
            ->assertCount('nameSearchResults', 1)
            ->call('selectAssistant', $assistant->id)
            ->assertSet('assistantExists', true)
            ->assertSet('searchedAssistant.id', $assistant->id)
            ->call('markAttendance')
            ->assertSet('searchDocument', '');

        $this->assertDatabaseHas('attendances', [
            'event_id' => $this->event->id,
            'assistant_id' => $assistant->id,
        ]);
    }

    public function test_can_delete_attendance_from_table(): void
    {
        $assistant = Assistant::create([
            'document' => '55667788',
            'name' => 'Carlos López',
            'birth_date' => '1990-01-01',
        ]);

        $attendance = Attendance::create([
            'event_id' => $this->event->id,
            'assistant_id' => $assistant->id,
        ]);

        $this->actingAs($this->user);

        Livewire::test(ManageAttendances::class)
            ->call('selectEvent', $this->event->id)
            ->callTableAction('delete', $attendance);

        $this->assertDatabaseMissing('attendances', [
            'id' => $attendance->id,
        ]);

        // Aseguramos que el asistente sigue existiendo en el sistema
        $this->assertDatabaseHas('assistants', [
            'id' => $assistant->id,
        ]);
    }
}
