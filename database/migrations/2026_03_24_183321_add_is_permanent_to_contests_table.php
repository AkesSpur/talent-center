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
            $table->boolean('is_permanent')->default(false)->after('evaluation_end_at');
            $table->dateTime('applications_end_at')->nullable()->change();
            $table->dateTime('evaluation_end_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('is_permanent');
            $table->dateTime('applications_end_at')->nullable(false)->change();
            $table->dateTime('evaluation_end_at')->nullable(false)->change();
        });
    }
};
