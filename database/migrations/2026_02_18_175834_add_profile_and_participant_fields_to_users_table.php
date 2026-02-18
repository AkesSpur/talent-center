<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Profile fields
            $table->text('bio')->nullable()->after('phone');
            $table->string('city', 255)->nullable()->after('bio');
            $table->string('country', 255)->nullable()->after('city');
            $table->string('education', 255)->nullable()->after('country');

            // Participant (child) fields
            $table->date('birth_date')->nullable()->after('education');
            $table->string('organization', 255)->nullable()->after('birth_date');
            $table->string('group', 255)->nullable()->after('organization');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bio', 'city', 'country', 'education', 'birth_date', 'organization', 'group']);
        });
    }
};
