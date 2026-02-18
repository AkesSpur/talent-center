<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
// class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'patronymic',
        'email',
        'phone',
        'password',
        'role',
        'parent_id',
        'email_notifications',
        'is_blocked',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'email_notifications' => 'boolean',
            'is_blocked' => 'boolean',
        ];
    }

    // ── Accessors ──────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        $parts = [$this->last_name, $this->first_name];
        if ($this->patronymic) {
            $parts[] = $this->patronymic;
        }

        return implode(' ', $parts);
    }

    // ── Role Helpers ───────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isSupport(): bool
    {
        return $this->role === UserRole::Support;
    }

    public function isParticipant(): bool
    {
        return $this->role === UserRole::Participant;
    }

    public function isBlocked(): bool
    {
        return $this->is_blocked;
    }

    // ── Organization Permission Helpers ────────────────

    public function organizationPermissions(Organization $org): ?object
    {
        $pivot = $this->organizations()->where('organization_id', $org->id)->first()?->pivot;

        if (! $pivot) {
            return null;
        }

        return (object) [
            'can_create' => (bool) $pivot->can_create,
            'can_manage' => (bool) $pivot->can_manage,
            'can_evaluate' => (bool) $pivot->can_evaluate,
        ];
    }

    public function canInOrg(string $permission, Organization $org): bool
    {
        $perms = $this->organizationPermissions($org);

        if (! $perms) {
            return false;
        }

        $key = "can_{$permission}";

        return property_exists($perms, $key) && $perms->{$key};
    }

    public function isOrgAdmin(Organization $org): bool
    {
        $perms = $this->organizationPermissions($org);

        if (! $perms) {
            return false;
        }

        return $perms->can_create && $perms->can_manage && $perms->can_evaluate;
    }

    // ── Relationships ──────────────────────────────────

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot(['can_create', 'can_manage', 'can_evaluate'])
            ->withTimestamps();
    }

    public function createdOrganizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function diplomas(): HasMany
    {
        return $this->hasMany(Diploma::class);
    }

    public function actionLogs(): HasMany
    {
        return $this->hasMany(ActionLog::class);
    }
}
