<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contest extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'created_by',
        'title',
        'description',
        'rules',
        'status',
        'applications_start_at',
        'applications_end_at',
        'evaluation_end_at',
        'diploma_background',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContestStatus::class,
            'applications_start_at' => 'datetime',
            'applications_end_at' => 'datetime',
            'evaluation_end_at' => 'datetime',
        ];
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
}
