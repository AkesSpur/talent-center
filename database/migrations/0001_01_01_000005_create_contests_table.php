<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description');
            $table->text('rules')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('applications_start_at');
            $table->timestamp('applications_end_at');
            $table->timestamp('evaluation_end_at');
            $table->string('diploma_background', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contests');
    }
};
