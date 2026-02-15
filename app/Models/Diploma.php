<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diploma extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'user_id',
        'contest_id',
        'file_path',
        'is_preview',
    ];

    protected function casts(): array
    {
        return [
            'is_preview' => 'boolean',
        ];
    }

    // ── Relationships ──────────────────────────────────

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }
}
