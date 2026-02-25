<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('diploma_background');
            $table->foreignId('platform_category_id')
                ->nullable()
                ->after('cover_image')
                ->constrained('platform_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropForeign(['platform_category_id']);
            $table->dropColumn(['cover_image', 'platform_category_id']);
        });
    }
};
