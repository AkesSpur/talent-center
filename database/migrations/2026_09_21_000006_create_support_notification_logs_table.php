<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->nullable()
                ->constrained('support_tickets')->cascadeOnDelete();
            $table->string('recipient_email');
            $table->string('template_type', 64);
            $table->string('status', 16);
            $table->text('error')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_notification_logs');
    }
};
