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
            $table->boolean('is_manual_status')->default(false)->after('status');
            $table->index(['is_manual_status', 'status', 'event_date'], 'events_status_sync_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_status_sync_index');
            $table->dropColumn('is_manual_status');
        });
    }
};
