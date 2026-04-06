<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'contest_id',
        'user_id',
        'payout_registry_id',
        'tbank_order_id',
        'tbank_payment_id',
        'amount',
        'status',
        'receipt_data',
    ];

    protected function casts(): array
    {
        return [
            'status'       => PaymentStatus::class,
            'receipt_data' => 'array',
        ];
    }

    // ── Relationships ──────────────────────────────────

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payoutRegistry(): BelongsTo
    {
        return $this->belongsTo(PayoutRegistry::class);
    }
}
