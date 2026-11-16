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
            $table->uuid('id')->primary();
            $table->foreignUuid('package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('theme_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('category')->default('wedding');
            $table->string('status')->default('draft');
            $table->string('gallery_visibility')->default('public');
            $table->string('party_one_name');
            $table->string('party_two_name')->nullable();
            $table->date('event_date')->nullable();
            $table->string('venue')->nullable();
            $table->json('custom_colors')->nullable();
            $table->string('cover_path')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
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
