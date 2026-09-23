<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SupportAttachment;
use App\Services\SupportAttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportAttachmentController extends Controller
{
    /**
     * Attachments live on a private disk; these actions are the only way to
     * read them. Downloads go out as attachments with sniffing disabled, so an
     * uploaded file can never be rendered as active content in the browser.
     */
    public function download(Request $request, string $token): StreamedResponse
    {
        $attachment = $this->authorizedAttachment($token);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name, [
            'X-Content-Type-Options' => 'nosniff',
            'Content-Type'           => $attachment->mime_type,
        ]);
    }

    /**
     * Inline view for the lightbox. Only JPEG/PNG/GIF/WebP — judged by the
     * sniffed type — ever render in the page; every other file stays a download.
     */
    public function preview(Request $request, string $token): StreamedResponse
    {
        $attachment = $this->authorizedAttachment($token);

        abort_unless($attachment->isPreviewable(), 404);

        return $this->inline($attachment, $attachment->path, $attachment->mime_type);
    }

    /** The conversation tile: a small copy, or the original if one can't be made. */
    public function thumbnail(Request $request, string $token, SupportAttachmentService $files): StreamedResponse
    {
        $attachment = $this->authorizedAttachment($token);

        abort_unless($attachment->isPreviewable(), 404);

        $thumb = $files->thumbnail($attachment);

        return $thumb !== null
            ? $this->inline($attachment, $thumb, 'image/jpeg')
            : $this->inline($attachment, $attachment->path, $attachment->mime_type);
    }

    private function authorizedAttachment(string $token): SupportAttachment
    {
        $attachment = SupportAttachment::where('token', $token)->firstOrFail();

        $this->authorize('download', $attachment);

        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404, 'Файл не найден.');

        return $attachment;
    }

    private function inline(SupportAttachment $attachment, string $path, string $mime): StreamedResponse
    {
        return Storage::disk($attachment->disk)->response($path, $attachment->original_name, [
            'Content-Type'            => $mime,
            'X-Content-Type-Options'  => 'nosniff',
            // Opened on its own in a tab, the image still can't run or load anything.
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox",
            // Tokens never point at different content, and the cache is this browser's only.
            'Cache-Control'           => 'private, max-age=86400',
        ]);
    }
}
