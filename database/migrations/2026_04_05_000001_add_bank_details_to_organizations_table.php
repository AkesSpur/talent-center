<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('city');
            $table->string('bank_bik', 9)->nullable()->after('bank_name');
            $table->string('bank_account', 20)->nullable()->after('bank_bik');
            $table->string('correspondent_account', 20)->nullable()->after('bank_account');
            $table->string('kpp', 9)->nullable()->after('correspondent_account');
            $table->boolean('offer_accepted')->default(false)->after('kpp');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_bik', 'bank_account', 'correspondent_account', 'kpp', 'offer_accepted']);
        });
    }
};
