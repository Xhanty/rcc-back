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
        Schema::table('users', function (Blueprint $table) {
            $table->json('modules')->nullable()->after('password');
            $table->boolean('is_super_admin')->default(false)->after('modules');
        });

        // Set the first user as super admin by default so they don't lose access
        $firstUser = \App\Models\User::first();
        if ($firstUser) {
            $firstUser->update([
                'is_super_admin' => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['modules', 'is_super_admin']);
        });
    }
};
