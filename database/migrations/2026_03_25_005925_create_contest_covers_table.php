<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contest_covers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('image_path', 500);
            $table->timestamps();
        });

        Schema::create('contest_cover_platform_category', function (Blueprint $table) {
            $table->foreignId('contest_cover_id')
                ->constrained('contest_covers')
                ->cascadeOnDelete();
            $table->foreignId('platform_category_id')
                ->constrained('platform_categories')
                ->cascadeOnDelete();
            $table->primary(['contest_cover_id', 'platform_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contest_cover_platform_category');
        Schema::dropIfExists('contest_covers');
    }
};
