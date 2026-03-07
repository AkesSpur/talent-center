<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ContestStatus;
use App\Models\Contest;
use App\Services\ActionLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TransitionContestStatuses extends Command
{
    protected $signature = 'contests:transition';

    protected $description = 'Automatically transition contest statuses based on current date.';

    public function handle(): int
    {
        $now         = Carbon::now();
        $transitioned = 0;

        // pending → accepting: applications_start_at <= now
        Contest::where('status', ContestStatus::Pending->value)
            ->where('applications_start_at', '<=', $now)
            ->each(function (Contest $contest) use (&$transitioned): void {
                $contest->update(['status' => ContestStatus::Accepting->value]);
                ActionLogService::log('contest.status_transition', $contest, [
                    'from' => ContestStatus::Pending->value,
                    'to'   => ContestStatus::Accepting->value,
                ]);
                $transitioned++;
            });

        // accepting → evaluation: applications_end_at < now
        Contest::where('status', ContestStatus::Accepting->value)
            ->where('applications_end_at', '<', $now)
            ->each(function (Contest $contest) use (&$transitioned): void {
                $contest->update(['status' => ContestStatus::Evaluation->value]);
                ActionLogService::log('contest.status_transition', $contest, [
                    'from' => ContestStatus::Accepting->value,
                    'to'   => ContestStatus::Evaluation->value,
                ]);
                $transitioned++;
            });

        // Note: evaluation → archive is manual only (via EvaluationController::finalize)
        // to ensure diplomas are always generated during finalization.

        $this->info("Transitioned {$transitioned} contest(s).");

        return Command::SUCCESS;
    }
}
