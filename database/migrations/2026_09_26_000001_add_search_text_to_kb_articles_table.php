<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A lowercased copy of the title and body for searching.
 *
 * `LIKE` folds case differently per driver: MySQL's utf8mb4_unicode_ci matches
 * «Возврат» for «возврат», SQLite only folds ASCII and does not. Searching a
 * column that is already lowercase makes the two behave the same, so what the
 * tests prove is what production does.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->longText('search_text')->nullable()->after('content_text');
        });
    }

    public function down(): void
    {
        Schema::table('kb_articles', function (Blueprint $table) {
            $table->dropColumn('search_text');
        });
    }
};
