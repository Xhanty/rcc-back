<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_prayer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('prayer_id')->constrained('prayers')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'prayer_id']);
        });

        // Migrar relaciones existentes desde events.prayer_id hacia la tabla pivote
        if (Schema::hasColumn('events', 'prayer_id')) {
            $eventsWithPrayer = DB::table('events')
                ->whereNotNull('prayer_id')
                ->select('id', 'prayer_id')
                ->get();

            $now = now();
            foreach ($eventsWithPrayer as $event) {
                DB::table('event_prayer')->insertOrIgnore([
                    'event_id' => $event->id,
                    'prayer_id' => $event->prayer_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_prayer');
    }
};
