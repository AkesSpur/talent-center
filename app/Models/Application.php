<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\FileType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'contest_id',
        'category_id',
        'user_id',
        'status',
        'rejection_reason',
        'file_path',
        'file_type',
        'external_link',
        'evaluated_by',
        'evaluated_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'file_type' => FileType::class,
            'evaluated_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ContestCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function evaluatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    public function diploma(): HasOne
    {
        return $this->hasOne(Diploma::class);
    }
}
