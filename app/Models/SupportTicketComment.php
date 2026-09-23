<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SupportTicketComment extends Model
{
    public const AUTHOR_USER   = 'user';
    public const AUTHOR_ADMIN  = 'admin';
    public const AUTHOR_SYSTEM = 'system';

    protected $fillable = [
        'ticket_id',
        'author_id',
        'author_type',
        'content',
        'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(SupportAttachment::class, 'attachable');
    }

    public function isFromUser(): bool
    {
        return $this->author_type === self::AUTHOR_USER;
    }
}
