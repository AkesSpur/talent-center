<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Contest;
use App\Models\Diploma;
use App\Models\SiteSettings;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
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
        set_time_limit(120);

        $application->load(['user', 'contest.organization', 'category', 'ageGroup']);

        // ── Diploma number ──────────────────────────────────
        $diplomaNumber = $application->contest_id . '-' . str_pad((string) $application->id, 7, '0', STR_PAD_LEFT);

        // ── QR code as PNG data URI ─────────────────────────
        $verifyUrl     = route('diplomvtrifi.show', $diplomaNumber);
        $qrCodeDataUri = $this->generateQrCodePng($verifyUrl);

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

        // ── Font paths (forward slashes required for dompdf CSS url()) ───
        $fontAriblk  = str_replace('\\', '/', public_path('fonts/ariblk.ttf'));
        $fontCalibri = str_replace('\\', '/', public_path('fonts/calibri.ttf'));

        $html = view('diplomas.template', [
            'participantName' => $application->user->full_name,
            'participantLastName' => $application->user->last_name,
            'participantFirstPatronymic' => trim(($application->user->first_name ?? '') . ' ' . ($application->user->patronymic ?? '')),
            'contestTitle'    => $application->contest->title,
            'orgName'         => $application->contest->organization->name,
            'orgCity'         => $application->contest->organization->city,
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
            'fontAriblk'      => $fontAriblk,
            'fontCalibri'     => $fontCalibri,
            'contactEmail'    => SiteSettings::get(SiteSettings::CONTACT_EMAIL, 'info@talant-centr.ru'),
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
     * Generate a QR code as an inline PNG data URI using GD.
     * PNG is rendered natively by dompdf, far faster than SVG.
     */
    private function generateQrCodePng(string $content): string
    {
        $qrCode = Encoder::encode($content, ErrorCorrectionLevel::L());
        $matrix = $qrCode->getMatrix();

        $moduleCount = $matrix->getWidth();
        $moduleSize  = 6;  // pixels per module
        $margin      = 4;  // quiet zone in modules
        $imgSize     = ($moduleCount + $margin * 2) * $moduleSize;

        $image = imagecreatetruecolor($imgSize, $imgSize);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefilledrectangle($image, 0, 0, $imgSize - 1, $imgSize - 1, $transparent);
        imagealphablending($image, true);
        $black = imagecolorallocate($image, 0, 0, 0);

        for ($y = 0; $y < $moduleCount; $y++) {
            for ($x = 0; $x < $moduleCount; $x++) {
                if ($matrix->get($x, $y) === 1) {
                    $px = ($x + $margin) * $moduleSize;
                    $py = ($y + $margin) * $moduleSize;
                    imagefilledrectangle($image, $px, $py, $px + $moduleSize - 1, $py + $moduleSize - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode($png);
    }
}
