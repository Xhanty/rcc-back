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
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('prayer_id')->nullable()->after('event_type_id')->constrained('prayers')->nullOnDelete();
        });

        Schema::create('event_song', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('song_id')->constrained('songs')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['event_id', 'song_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_song');

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['prayer_id']);
            $table->dropColumn('prayer_id');
        });
    }
};
