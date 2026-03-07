<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diploma_backgrounds', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->string('image_path', 500);
            $table->timestamps();
        });

        Schema::create('diploma_bg_platform_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diploma_background_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_category_id')->constrained()->cascadeOnDelete();
            $table->unique(['diploma_background_id', 'platform_category_id'], 'db_pc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diploma_bg_platform_category');
        Schema::dropIfExists('diploma_backgrounds');
    }
};
