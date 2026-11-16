<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contributions', function (Blueprint $table) {
            $table->string('visibility')->default('public')->index();
            $table->string('file_disk')->default('public');
        });
        Schema::table('events', function (Blueprint $table) {
            $table->string('owner_access_token', 64)->nullable()->unique();
        });
        DB::table('events')->select('id')->orderBy('id')->chunk(100, function ($events) {
            foreach ($events as $event) {
                DB::table('events')->where('id', $event->id)->update(['owner_access_token' => Str::random(64)]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('contributions', fn (Blueprint $table) => $table->dropColumn(['visibility', 'file_disk']));
        Schema::table('events', fn (Blueprint $table) => $table->dropColumn('owner_access_token'));
    }
};
