<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Contest;
use App\Models\Diploma;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
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
        $application->load(['user', 'contest.organization', 'category', 'ageGroup']);

        // ── Diploma number ──────────────────────────────────
        $diplomaNumber = $application->contest_id . '-' . str_pad((string) $application->id, 7, '0', STR_PAD_LEFT);

        // ── QR code as SVG data URI ─────────────────────────
        $verifyUrl    = route('diplomvtrifi.show', $diplomaNumber);
        $qrCodeDataUri = $this->generateQrCodeSvg($verifyUrl);

        // ── Jury list ───────────────────────────────────────
        $juryMembers = $application->contest->organization
            ->representatives()
            ->wherePivot('can_evaluate', true)
            ->get()
            ->map(fn ($u) => $u->full_name)
            ->all();

        // ── Background image ────────────────────────────────
        $backgroundPath = null;
        if ($application->contest->diploma_background) {
            $backgroundPath = Storage::disk('public')->path($application->contest->diploma_background);
            if (! file_exists($backgroundPath)) {
                $backgroundPath = null;
            }
        }

        $html = view('diplomas.template', [
            'participantName' => $application->user->full_name,
            'participantLastName' => $application->user->last_name,
            'participantFirstPatronymic' => trim(($application->user->first_name ?? '') . ' ' . ($application->user->patronymic ?? '')),
            'contestTitle'    => $application->contest->title,
            'orgName'         => $application->contest->organization->name,
            'categoryName'    => $application->category?->name,
            'ageGroupName'    => $application->ageGroup?->name,
            'statusLabel'     => $application->status->diplomaLabel(),
            'teacherName'     => $application->teacher_name,
            'awardedDate'     => $this->russianMonthYear(
                $application->evaluated_at ?? now()
            ),
            'diplomaNumber'   => $diplomaNumber,
            'qrCodeDataUri'   => $qrCodeDataUri,
            'juryMembers'     => $juryMembers,
            'backgroundPath'  => $backgroundPath,
        ])->render();

        $pdf = Pdf::loadHTML($html, 'UTF-8')->setPaper('a4', 'portrait');

        $directory = 'diplomas';
        if (! Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $filename = "diplomas/{$application->id}.pdf";
        Storage::disk('public')->put($filename, $pdf->output());

        $diploma = Diploma::updateOrCreate(
            ['application_id' => $application->id],
            [
                'user_id'        => $application->user_id,
                'contest_id'     => $application->contest_id,
                'file_path'      => $filename,
                'is_preview'     => false,
                'diploma_number' => $diplomaNumber,
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
            ->with(['user', 'category', 'ageGroup', 'contest.organization'])
            ->whereIn('status', $eligibleStatuses)
            ->each(function (Application $application) use (&$count): void {
                $this->generateForApplication($application);
                $count++;
            });

        return $count;
    }

    /**
     * Return a Russian month + year string, e.g. "март 2026".
     */
    private function russianMonthYear(\DateTimeInterface $date): string
    {
        $months = [
            1 => 'январь', 2 => 'февраль', 3 => 'март',
            4 => 'апрель', 5 => 'май', 6 => 'июнь',
            7 => 'июль', 8 => 'август', 9 => 'сентябрь',
            10 => 'октябрь', 11 => 'ноябрь', 12 => 'декабрь',
        ];

        return $months[(int) $date->format('n')] . ' ' . $date->format('Y');
    }

    /**
     * Generate a QR code as an inline SVG data URI.
     */
    private function generateQrCodeSvg(string $content): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $svg    = $writer->writeString($content, Encoder::DEFAULT_BYTE_MODE_ECODING);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
