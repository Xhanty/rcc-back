<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('accepted_terms')->default(false)->after('status');
            $table->timestamp('terms_accepted_at')->nullable()->after('accepted_terms');
            $table->string('terms_version', 20)->nullable()->after('terms_accepted_at');
            $table->string('consent_ip', 45)->nullable()->after('terms_version');
            $table->string('consent_user_agent', 512)->nullable()->after('consent_ip');
        });

        Schema::table('petitions', function (Blueprint $table) {
            $table->boolean('accepted_terms')->default(false)->after('status');
            $table->timestamp('terms_accepted_at')->nullable()->after('accepted_terms');
            $table->string('terms_version', 20)->nullable()->after('terms_accepted_at');
            $table->string('consent_ip', 45)->nullable()->after('terms_version');
            $table->string('consent_user_agent', 512)->nullable()->after('consent_ip');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'accepted_terms',
                'terms_accepted_at',
                'terms_version',
                'consent_ip',
                'consent_user_agent',
            ]);
        });

        Schema::table('petitions', function (Blueprint $table) {
            $table->dropColumn([
                'accepted_terms',
                'terms_accepted_at',
                'terms_version',
                'consent_ip',
                'consent_user_agent',
            ]);
        });
    }
};
