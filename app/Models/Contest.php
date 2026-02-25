<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Contest extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'platform_category_id',
        'created_by',
        'title',
        'description',
        'rules',
        'status',
        'applications_start_at',
        'applications_end_at',
        'evaluation_end_at',
        'diploma_background',
        'cover_image',
    ];

    protected function casts(): array
    {
        return [
            'status'                => ContestStatus::class,
            'applications_start_at' => 'datetime',
            'applications_end_at'   => 'datetime',
            'evaluation_end_at'     => 'datetime',
        ];
    }

    // ── Status Determination ───────────────────────────

    /**
     * Compute the correct status based on current date vs. contest dates.
     * Called immediately after creation and after date updates.
     */
    public function determineCurrentStatus(): ContestStatus
    {
        $now = Carbon::now();

        if ($now->greaterThan($this->evaluation_end_at)) {
            return ContestStatus::Archive;
        }

        if ($now->greaterThan($this->applications_end_at)) {
            return ContestStatus::Evaluation;
        }

        if ($now->greaterThanOrEqualTo($this->applications_start_at)) {
            return ContestStatus::Accepting;
        }

        return ContestStatus::Pending;
    }

    // ── Status Helpers ─────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === ContestStatus::Pending;
    }

    public function isAccepting(): bool
    {
        return $this->status === ContestStatus::Accepting;
    }

    public function isEvaluation(): bool
    {
        return $this->status === ContestStatus::Evaluation;
    }

    public function isArchive(): bool
    {
        return $this->status === ContestStatus::Archive;
    }

    public function isCancelled(): bool
    {
        return $this->status === ContestStatus::Cancelled;
    }

    // ── Relationships ──────────────────────────────────

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(ContestCategory::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function platformCategory(): BelongsTo
    {
        return $this->belongsTo(PlatformCategory::class);
    }

    public function juries(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'contest_juries');
    }
}
