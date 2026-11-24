<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('occupation')->nullable()->after('avatar_url');
            $table->string('joined_since', 100)->nullable()->after('occupation');
            $table->text('testimony')->nullable()->after('joined_since');
            $table->string('favorite_verse', 300)->nullable()->after('testimony');
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->string('custom_occupation')->nullable()->after('display_title');
            $table->string('custom_joined_since', 100)->nullable()->after('custom_occupation');
            $table->text('custom_testimony')->nullable()->after('custom_joined_since');
            $table->string('custom_favorite_verse', 300)->nullable()->after('custom_testimony');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'occupation',
                'joined_since',
                'testimony',
                'favorite_verse',
            ]);
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn([
                'custom_occupation',
                'custom_joined_since',
                'custom_testimony',
                'custom_favorite_verse',
            ]);
        });
    }
};
