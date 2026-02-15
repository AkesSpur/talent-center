<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrganizationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'inn',
        'legal_address',
        'contact_email',
        'contact_phone',
        'status',
        'created_by',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrganizationStatus::class,
            'verified_at' => 'datetime',
        ];
    }

    // ── Helpers ────────────────────────────────────────

    public function isVerified(): bool
    {
        return $this->status === OrganizationStatus::Verified;
    }

    public function isPending(): bool
    {
        return $this->status === OrganizationStatus::Pending;
    }

    // ── Relationships ──────────────────────────────────

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function representatives(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot(['can_create', 'can_manage', 'can_evaluate'])
            ->withTimestamps();
    }

    public function contests(): HasMany
    {
        return $this->hasMany(Contest::class);
    }
}
