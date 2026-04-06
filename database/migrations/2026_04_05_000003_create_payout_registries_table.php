<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_registries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contest_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('payout_amount');
            $table->unsignedInteger('transfer_amount')->nullable();
            $table->string('transfer_document_path')->nullable();
            $table->date('transfer_document_date')->nullable();
            $table->string('transfer_document_number')->nullable();
            $table->boolean('payment_received')->default(false);
            $table->timestamp('payment_received_at')->nullable();
            $table->foreignId('payment_received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('contest_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_registries');
    }
};
