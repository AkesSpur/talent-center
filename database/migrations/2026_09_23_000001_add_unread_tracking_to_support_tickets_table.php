<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * «New message» marks: when each side last wrote, and when each side last
 * opened the ticket. A later message than the last visit means unread.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->timestamp('last_user_message_at')->nullable()->after('csat_submitted_at');
            $table->timestamp('last_staff_message_at')->nullable()->after('last_user_message_at');
            $table->timestamp('user_read_at')->nullable()->after('last_staff_message_at');
            $table->timestamp('staff_read_at')->nullable()->after('user_read_at');
        });

        // The opening message counts as the user's first one.
        DB::statement("
            update support_tickets
            set last_user_message_at = coalesce((
                select max(c.created_at) from support_ticket_comments c
                where c.ticket_id = support_tickets.id and c.author_type = 'user'
            ), created_at)
        ");

        DB::statement("
            update support_tickets
            set last_staff_message_at = (
                select max(c.created_at) from support_ticket_comments c
                where c.ticket_id = support_tickets.id and c.author_type = 'admin' and c.is_internal = 0
            )
        ");

        // Tickets that existed before this feature start as read on both sides,
        // so nobody meets a wall of «new» marks on the day it ships.
        DB::table('support_tickets')->update(['user_read_at' => now(), 'staff_read_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['last_user_message_at', 'last_staff_message_at', 'user_read_at', 'staff_read_at']);
        });
    }
};
