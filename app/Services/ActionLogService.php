<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActionLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActionLogService
{
    /**
     * Log an action.
     *
     * @param  array<string, mixed>|null  $metadata
     */
    public static function log(
        string $action,
        ?Model $target = null,
        ?array $metadata = null,
    ): ActionLog {
        return ActionLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'target_type' => $target ? $target->getMorphClass() : null,
            'target_id' => $target?->getKey(),
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'created_at' => now(),
        ]);
    }
}
