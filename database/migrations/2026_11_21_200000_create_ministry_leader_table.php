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
        Schema::create('ministry_leader', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ministry_id')->constrained('ministries')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ministry_id', 'user_id']);
        });

        if (Schema::hasColumn('ministries', 'leader_id')) {
            $existing = DB::table('ministries')
                ->whereNotNull('leader_id')
                ->select('id', 'leader_id')
                ->get();

            $now = now();
            foreach ($existing as $row) {
                DB::table('ministry_leader')->insertOrIgnore([
                    'ministry_id' => $row->id,
                    'user_id' => $row->leader_id,
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
        Schema::dropIfExists('ministry_leader');
    }
};
