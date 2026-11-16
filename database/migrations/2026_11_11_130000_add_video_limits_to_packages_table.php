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
            $table->boolean('allow_video')->default(false)->after('allow_custom_theme');
            $table->unsignedSmallInteger('max_video_seconds')->default(0)->after('allow_video');
            $table->unsignedInteger('max_video_kb')->default(0)->after('max_video_seconds');
        });

        DB::table('packages')
            ->where(function ($query) {
                $query->where('slug', 'plus')
                    ->orWhere(function ($q) {
                        $q->where('max_frames', '>=', 2)->where('max_frames', '<', 4);
                    });
            })
            ->update([
                'allow_video' => true,
                'max_video_seconds' => 20,
                'max_video_kb' => 15360,
            ]);

        DB::table('packages')
            ->where(function ($query) {
                $query->where('slug', 'full')
                    ->orWhere('max_frames', '>=', 4);
            })
            ->update([
                'allow_video' => true,
                'max_video_seconds' => 45,
                'max_video_kb' => 25600,
            ]);
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['allow_video', 'max_video_seconds', 'max_video_kb']);
        });
    }
};
