<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayoutRegistry extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'contest_id',
        'payout_amount',
        'transfer_amount',
        'transfer_document_path',
        'transfer_document_date',
        'transfer_document_number',
        'payment_received',
        'payment_received_at',
        'payment_received_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_received'    => 'boolean',
            'payment_received_at' => 'datetime',
            'transfer_document_date' => 'date',
        ];
    }

    // ── Relationships ──────────────────────────────────

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payment_received_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
