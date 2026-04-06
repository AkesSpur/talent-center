<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payout_registry_id')->nullable()->constrained('payout_registries')->nullOnDelete();
            $table->string('tbank_order_id')->unique();
            $table->string('tbank_payment_id')->nullable();
            $table->unsignedInteger('amount');
            $table->string('status')->default('accepted')->index();
            $table->json('receipt_data')->nullable();
            $table->timestamps();

            $table->index('application_id');
            $table->index('contest_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
