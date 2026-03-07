<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('age_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contest_category_id')->nullable()->constrained('contest_categories')->cascadeOnDelete();
            $table->string('name', 255);
            $table->unsignedSmallInteger('min_age')->nullable();
            $table->unsignedSmallInteger('max_age')->nullable();
            $table->timestamps();

            $table->index(['contest_id', 'contest_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('age_groups');
    }
};
