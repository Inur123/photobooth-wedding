<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contributions', function (Blueprint $table): void {
            $table->unsignedSmallInteger('width')->nullable()->after('thumbnail_path');
            $table->unsignedSmallInteger('height')->nullable()->after('width');
            $table->char('dominant_color', 7)->nullable()->after('height');
            $table->string('processing_status')->default('done')->after('size_bytes');
            $table->string('client_upload_id', 36)->nullable()->unique()->after('processing_status');
            $table->index(['event_id', 'is_approved', 'visibility', 'created_at'], 'contributions_gallery_index');
        });
    }

    public function down(): void
    {
        Schema::table('contributions', function (Blueprint $table): void {
            $table->dropIndex('contributions_gallery_index');
            $table->dropUnique(['client_upload_id']);
            $table->dropColumn(['width', 'height', 'dominant_color', 'processing_status', 'client_upload_id']);
        });
    }
};
