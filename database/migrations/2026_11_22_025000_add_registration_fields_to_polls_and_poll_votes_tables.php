<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->boolean('requires_registration')->default(false)->after('restrict_by_ip');
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->foreignId('assistant_id')->nullable()->after('poll_option_id')->constrained('assistants')->nullOnDelete();
            $table->string('document', 50)->nullable()->after('assistant_id')->index();
            $table->string('name')->nullable()->after('document');
            $table->string('email')->nullable()->after('name');
            $table->string('phone', 50)->nullable()->after('email');
            $table->date('birth_date')->nullable()->after('phone');
            $table->boolean('accepted_terms')->default(false)->after('ip_hash');
            $table->timestamp('terms_accepted_at')->nullable()->after('accepted_terms');
            $table->string('terms_version', 20)->nullable()->after('terms_accepted_at');
            $table->string('consent_ip', 45)->nullable()->after('terms_version');
            $table->string('consent_user_agent', 512)->nullable()->after('consent_ip');
        });

        Schema::table('assistants', function (Blueprint $table) {
            $table->boolean('accepted_terms')->default(false)->after('birth_date');
            $table->timestamp('terms_accepted_at')->nullable()->after('accepted_terms');
            $table->string('terms_version', 20)->nullable()->after('terms_accepted_at');
            $table->string('consent_ip', 45)->nullable()->after('terms_version');
            $table->string('consent_user_agent', 512)->nullable()->after('consent_ip');
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn('requires_registration');
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assistant_id');
            $table->dropColumn([
                'document',
                'name',
                'email',
                'phone',
                'birth_date',
                'accepted_terms',
                'terms_accepted_at',
                'terms_version',
                'consent_ip',
                'consent_user_agent',
            ]);
        });

        Schema::table('assistants', function (Blueprint $table) {
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
