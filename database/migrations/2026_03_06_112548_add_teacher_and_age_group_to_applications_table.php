<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('age_group_id')->nullable()->after('category_id')->constrained('age_groups')->nullOnDelete();
            $table->string('teacher_name', 255)->nullable()->after('external_link');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('age_group_id');
            $table->dropColumn('teacher_name');
        });
    }
};
