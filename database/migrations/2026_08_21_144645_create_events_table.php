<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // Relación con event_types
            $table->foreignId('event_type_id');

            // Información principal
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description', 500)->nullable();
            $table->longText('content')->nullable();
            $table->string('banner_image_path', 2048)->nullable();

            // Modalidad y ubicación
            $table->enum('modality', ['in_person', 'virtual'])->default('in_person');
            $table->string('venue_name')->nullable();
            $table->string('address')->nullable();
            $table->string('live_url')->nullable();

            // Fechas
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime')->nullable();

            // Control y visualización
            $table->enum('status', ['draft', 'published', 'not_published', 'cancelled', 'completed'])->default('draft');
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Índices compuestos para optimizar filtros de la Landing Page
            $table->index(['status', 'start_datetime']);
            $table->index(['is_featured', 'status']);

            $table->foreign('event_type_id')->references('id')->on('event_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
