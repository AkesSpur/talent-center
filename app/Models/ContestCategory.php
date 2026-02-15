<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContestCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'contest_id',
        'name',
        'description',
    ];

    // ── Relationships ──────────────────────────────────

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'category_id');
    }
}
