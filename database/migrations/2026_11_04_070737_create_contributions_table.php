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
        Schema::create('contributions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('frame_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->unsignedTinyInteger('layout')->nullable();
            $table->string('guest_name')->nullable();
            $table->text('message')->nullable();
            $table->string('file_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->index(['event_id', 'type']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
