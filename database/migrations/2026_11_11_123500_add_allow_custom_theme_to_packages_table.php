<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->boolean('allow_custom_theme')->default(false)->after('allow_zip_download');
        });

        DB::table('packages')
            ->whereIn('slug', ['plus', 'full'])
            ->orWhere('allow_zip_download', true)
            ->update(['allow_custom_theme' => true]);
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('allow_custom_theme');
        });
    }
};
