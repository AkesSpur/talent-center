<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
        });

        // Backfill slugs for existing contests
        $contests = DB::table('contests')->orderBy('id')->get(['id', 'title']);

        foreach ($contests as $contest) {
            $base = Str::slug($contest->title);
            if (empty($base)) {
                $base = 'contest';
            }

            $slug  = $base;
            $count = 2;
            while (DB::table('contests')->where('slug', $slug)->where('id', '!=', $contest->id)->exists()) {
                $slug = $base . '-' . $count++;
            }

            DB::table('contests')->where('id', $contest->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
