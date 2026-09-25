<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SupportNotificationTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per support email we tried to send (ТЗ 11.2), shown on the
 * «История уведомлений» tab of the operator's ticket card.
 */
class SupportNotificationLog extends Model
{
    /** The table carries created_at only. */
    public const UPDATED_AT = null;

    public const STATUS_SENT   = 'sent';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'ticket_id',
        'recipient_email',
        'template_type',
        'status',
        'error',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /** No enum cast on the column: an unrecognised old value must degrade, not throw. */
    public function getTemplateLabelAttribute(): string
    {
        return SupportNotificationTemplate::tryFrom($this->template_type)?->label()
            ?? $this->template_type;
    }

    public function wasSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }
}
