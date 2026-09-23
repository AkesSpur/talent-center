<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_email')->nullable();
            $table->foreignId('category_id')->constrained('support_categories');
            $table->foreignId('subcategory_id')->nullable()
                ->constrained('support_categories')->nullOnDelete();
            $table->string('subject', 150);
            $table->text('description');
            $table->string('status', 32)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sla_deadline')->nullable();
            $table->boolean('is_overdue')->default(false);
            $table->timestamp('overdue_notified_at')->nullable();
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('closed_reason', 32)->nullable();
            $table->unsignedTinyInteger('csat_score')->nullable();
            $table->timestamp('csat_submitted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['is_overdue', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['category_id', 'status']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
