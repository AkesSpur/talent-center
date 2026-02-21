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
        'avatar_path',
        'description',
        'inn',
        'ogrn',
        'legal_address',
        'website',
        'contact_email',
        'contact_phone',
        'status',
        'created_by',
        'verified_by',
        'verified_at',
        'is_blocked',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrganizationStatus::class,
            'verified_at' => 'datetime',
            'is_blocked' => 'boolean',
        ];
    }

    // ── Accessors ──────────────────────────────────────

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? asset('storage/' . $this->avatar_path) : null;
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));
        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
        }

        return mb_strtoupper(mb_substr($this->name, 0, 2));
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

    public function isBlocked(): bool
    {
        return $this->is_blocked;
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
