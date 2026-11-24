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
        Schema::create('social_media_items', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 50);
            $table->string('title', 255)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('url', 2048);
            $table->string('embed_url', 2048)->nullable();
            $table->string('thumbnail_url', 2048)->nullable();
            $table->string('author_name', 255)->nullable();
            $table->boolean('show_in_community')->default(true);
            $table->boolean('show_in_footer')->default(false);
            $table->integer('sort_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'show_in_community', 'show_in_footer', 'sort_order'], 'social_media_visibility_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_items');
    }
};
