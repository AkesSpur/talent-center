<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SupportTicketStatus;
use App\Enums\TicketCloseReason;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'guest_email',
        'category_id',
        'subcategory_id',
        'subject',
        'description',
        'status',
        'assigned_to',
        'last_operator_id',
        'sla_deadline',
        'is_overdue',
        'overdue_notified_at',
        'first_responded_at',
        'resolved_at',
        'closed_at',
        'closed_reason',
        'csat_score',
        'csat_submitted_at',
        'last_user_message_at',
        'last_staff_message_at',
        'user_read_at',
        'staff_read_at',
    ];

    protected function casts(): array
    {
        return [
            'status'              => SupportTicketStatus::class,
            'closed_reason'       => TicketCloseReason::class,
            'sla_deadline'        => 'datetime',
            'overdue_notified_at' => 'datetime',
            'first_responded_at'  => 'datetime',
            'resolved_at'         => 'datetime',
            'closed_at'           => 'datetime',
            'csat_submitted_at'   => 'datetime',
            'is_overdue'          => 'boolean',
            'csat_score'          => 'integer',
            'last_user_message_at'  => 'datetime',
            'last_staff_message_at' => 'datetime',
            'user_read_at'          => 'datetime',
            'staff_read_at'         => 'datetime',
        ];
    }

    // ── Accessors ──────────────────────────────────────

    /** Display number, e.g. #0042. */
    public function getNumberAttribute(): string
    {
        return '#' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getContactEmailAttribute(): ?string
    {
        return $this->user?->email ?? $this->guest_email;
    }

    // ── Scopes ─────────────────────────────────────────

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            SupportTicketStatus::New->value,
            SupportTicketStatus::InProgress->value,
            SupportTicketStatus::NeedsClarification->value,
        ]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->open()->where('sla_deadline', '<', now());
    }

    /** Tickets where support wrote after the owner last opened them. */
    public function scopeUnreadForOwner(Builder $query): Builder
    {
        return $query->whereNotNull('last_staff_message_at')
            ->where(fn (Builder $q) => $q
                ->whereNull('user_read_at')
                ->orWhereColumn('last_staff_message_at', '>', 'user_read_at'));
    }

    /** Tickets nobody on the helpdesk has opened, or where the user wrote since. */
    public function scopeUnreadForStaff(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->whereNull('staff_read_at')
            ->orWhereColumn('last_user_message_at', '>', 'staff_read_at'));
    }

    // ── Relationships ──────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class, 'category_id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class, 'subcategory_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function lastOperator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_operator_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(SupportTicketComment::class, 'ticket_id');
    }

    /** Files attached to the opening message. */
    public function attachments(): MorphMany
    {
        return $this->morphMany(SupportAttachment::class, 'attachable');
    }

    /** Comments a regular user is allowed to see (internal notes excluded). */
    public function publicComments(): HasMany
    {
        return $this->comments()->where('is_internal', false);
    }

    // ── Helpers ────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }

    /** A reply from support the owner hasn't opened yet. */
    public function hasUnreadForOwner(): bool
    {
        return $this->last_staff_message_at !== null
            && ($this->user_read_at === null || $this->last_staff_message_at->gt($this->user_read_at));
    }

    /**
     * Something waiting for the operators: a message from the user since their
     * last visit, or a ticket none of them has opened at all.
     */
    public function hasUnreadForStaff(): bool
    {
        return $this->staff_read_at === null
            || ($this->last_user_message_at !== null && $this->last_user_message_at->gt($this->staff_read_at));
    }

    public function isOverdueNow(): bool
    {
        return $this->isOpen()
            && $this->sla_deadline !== null
            && $this->sla_deadline->isPast();
    }

    /**
     * A ticket can be rated once it has been resolved (TZ 2.1), and stays
     * rateable after it closes — whoever closed it. «Закрыта» is only reached
     * from «Решена» (TZ 5.2), so every closed ticket was resolved first, whether
     * the user confirmed, an operator closed it or the 3-day auto-close ran.
     */
    public function canBeRated(): bool
    {
        return in_array($this->status, [SupportTicketStatus::Resolved, SupportTicketStatus::Closed], true);
    }
}
