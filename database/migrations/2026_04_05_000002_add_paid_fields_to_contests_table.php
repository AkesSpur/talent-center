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
            $table->boolean('is_paid')->default(false)->after('is_permanent');
            $table->unsignedInteger('entry_fee')->nullable()->after('is_paid');
            $table->unsignedSmallInteger('application_limit')->default(50)->after('entry_fee');
        });
    }

    public function down(): void
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn(['is_paid', 'entry_fee', 'application_limit']);
        });
    }
};
