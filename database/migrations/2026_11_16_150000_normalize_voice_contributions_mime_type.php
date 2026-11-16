<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Koreksi mime_type rekaman suara yang sempat tersimpan sebagai video/webm
        // agar mobile browser (Safari iOS) tidak menolak pemutarannya di elemen <audio>.
        DB::table('contributions')
            ->where('type', 'voice')
            ->where('mime_type', 'like', 'video/%')
            ->update(['mime_type' => 'audio/webm']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op karena normalisasi audio tidak perlu dibatalkan
    }
};
