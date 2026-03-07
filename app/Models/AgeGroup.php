<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgeGroup extends Model
{
    protected $fillable = [
        'contest_id',
        'contest_category_id',
        'name',
        'min_age',
        'max_age',
    ];

    protected $casts = [
        'min_age' => 'integer',
        'max_age' => 'integer',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function contestCategory(): BelongsTo
    {
        return $this->belongsTo(ContestCategory::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
