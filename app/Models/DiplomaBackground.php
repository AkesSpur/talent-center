<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DiplomaBackground extends Model
{
    protected $fillable = [
        'name',
        'image_path',
    ];

    public function platformCategories(): BelongsToMany
    {
        return $this->belongsToMany(PlatformCategory::class, 'diploma_bg_platform_category');
    }
}
