<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Contest;
use App\Models\Diploma;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DiplomaService
{
    /**
     * Generate (or regenerate) the final diploma PDF for a placed application.
     * Creates or updates the Diploma record.
     * Only called for applications with Place1/Place2/Place3/Participant status.
     */
    public function generateForApplication(Application $application): Diploma
    {
        $application->load(['user', 'contest.organization', 'category']);

        $backgroundPath = null;
        if ($application->contest->diploma_background) {
            $backgroundPath = Storage::disk('public')->path($application->contest->diploma_background);
            if (! file_exists($backgroundPath)) {
                $backgroundPath = null;
            }
        }

        $html = view('diplomas.template', [
            'participantName' => $application->user->full_name,
            'contestTitle'    => $application->contest->title,
            'orgName'         => $application->contest->organization->name,
            'categoryName'    => $application->category?->name,
            'statusLabel'     => $application->status->label(),
            'teacherName'     => $application->teacher_name,
            'awardedDate'     => $application->evaluated_at
                ? $application->evaluated_at->format('d.m.Y')
                : now()->format('d.m.Y'),
            'backgroundPath'  => $backgroundPath,
        ])->render();

        $pdf = Pdf::loadHTML($html, 'UTF-8')->setPaper('a4', 'landscape');

        $directory = 'diplomas';
        if (! Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $filename = "diplomas/{$application->id}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        $diploma = Diploma::updateOrCreate(
            ['application_id' => $application->id],
            [
                'user_id'    => $application->user_id,
                'contest_id' => $application->contest_id,
                'file_path'  => $filename,
                'is_preview' => false,
            ]
        );

        return $diploma;
    }

    /**
     * Generate diplomas for all eligible applications in a contest.
     * Called during finalization and admin re-trigger.
     * Returns count of diplomas generated.
     */
    public function generateForContest(Contest $contest): int
    {
        $count = 0;

        $eligibleStatuses = [
            ApplicationStatus::Participant->value,
            ApplicationStatus::Place1->value,
            ApplicationStatus::Place2->value,
            ApplicationStatus::Place3->value,
        ];

        $contest->applications()
            ->with(['user', 'category', 'contest.organization'])
            ->whereIn('status', $eligibleStatuses)
            ->each(function (Application $application) use (&$count): void {
                $this->generateForApplication($application);
                $count++;
            });

        return $count;
    }
}
