<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SupportAttachment;
use GdImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SupportAttachmentService
{
    public const DISK = 'support';

    /** Per-file and per-request limits from the TZ (section 3.1). */
    public const MAX_FILES = 5;
    public const MAX_FILE_KB = 10240;   // 10 MB
    public const MAX_TOTAL_KB = 20480;  // 20 MB

    /** What the picker offers. The server re-checks every file by its content, not its name. */
    public const EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'];

    public const EXTENSIONS_TEXT = 'JPG, PNG, GIF, WebP, PDF, DOC, DOCX, XLS, XLSX, TXT и ZIP';

    /** Shown inline (thumbnail + lightbox). Everything else, SVG included, is download-only. */
    public const PREVIEWABLE_MIMES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    /** Room kept inside post_max_size for the message text and the multipart framing. */
    private const REQUEST_OVERHEAD_BYTES = 256 * 1024;

    /** Longest side of a thumbnail, px. Tiles are at most 128 CSS px, so this stays sharp at 2x. */
    private const THUMB_BOX = 480;

    /**
     * Store uploaded files on the private disk and attach them to a model.
     *
     * @param  array<int, UploadedFile>  $files
     * @return array<int, SupportAttachment>
     */
    public function attachMany(Model $attachable, array $files, string $folder): array
    {
        $created = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store($folder, self::DISK);

            $created[] = $attachable->attachments()->create([
                'disk'          => self::DISK,
                'path'          => $path,
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                // Sniffed from content (finfo), never the client-supplied header.
                'mime_type'     => $file->getMimeType() ?: 'application/octet-stream',
                'size_bytes'    => $file->getSize(),
                'uploaded_by'   => Auth::id(),
            ]);
        }

        return $created;
    }

    // ── Limits actually in force ───────────────────────

    /**
     * The TZ's 10 MB per file — or less when PHP on this server is set lower,
     * so the form never promises more than the server will accept.
     */
    public static function maxFileBytes(): int
    {
        return min(self::MAX_FILE_KB * 1024, self::iniBytes('upload_max_filesize'), self::maxTotalBytes());
    }

    /** The TZ's 20 MB per message, capped the same way by post_max_size. */
    public static function maxTotalBytes(): int
    {
        return max(1, min(self::MAX_TOTAL_KB * 1024, self::iniBytes('post_max_size') - self::REQUEST_OVERHEAD_BYTES));
    }

    /** «До 5 файлов, до 10 МБ каждый, всего до 20 МБ» */
    public static function limitsText(): string
    {
        return 'До ' . self::MAX_FILES . ' файлов, до ' . self::formatBytes(self::maxFileBytes())
            . ' каждый, всего до ' . self::formatBytes(self::maxTotalBytes());
    }

    /**
     * Settings for the upload picker (resources/js/support-upload.js).
     *
     * @return array<string, mixed>
     */
    public static function pickerConfig(): array
    {
        return [
            'maxFiles'       => self::MAX_FILES,
            'maxFileBytes'   => self::maxFileBytes(),
            'maxTotalBytes'  => self::maxTotalBytes(),
            'extensions'     => self::EXTENSIONS,
            'extensionsText' => self::EXTENSIONS_TEXT,
        ];
    }

    /** A php.ini size such as "12M" in bytes; 0 or less means PHP applies no limit. */
    private static function iniBytes(string $key): int
    {
        $bytes = ini_parse_quantity((string) ini_get($key) ?: '0');

        return $bytes > 0 ? $bytes : PHP_INT_MAX;
    }

    /**
     * «49 Б», «93 КБ», «3,8 МБ». The picker formats sizes by the same rules
     * (resources/js/support-upload.js), so a file reads the same before and after sending.
     */
    public static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' Б';
        }

        $kb = $bytes / 1024;

        if ($kb < 1023.5) {
            return max(1, (int) round($kb)) . ' КБ';
        }

        $mb = number_format(round($kb / 1024, 1), 1, ',', '');

        return preg_replace('/,0$/', '', $mb) . ' МБ';
    }

    // ── Thumbnails ─────────────────────────────────────

    /**
     * A small JPEG copy for the conversation view, made on first request and
     * kept on the private disk next to the originals. Returns null when the
     * image can't be decoded safely (unknown format, or more pixels than the
     * memory limit allows) — the caller then serves the original.
     */
    public function thumbnail(SupportAttachment $attachment): ?string
    {
        if (! $attachment->isPreviewable()) {
            return null;
        }

        $disk = Storage::disk($attachment->disk);
        $thumb = 'thumbs/' . $attachment->token . '.jpg';

        if ($disk->exists($thumb)) {
            return $thumb;
        }

        if (! extension_loaded('gd') || ! $disk->exists($attachment->path)) {
            return null;
        }

        $source = $disk->path($attachment->path);
        $info = @getimagesize($source);

        if ($info === false || ! $this->fitsInMemory($info[0], $info[1])) {
            return null;
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG  => @imagecreatefrompng($source),
            IMAGETYPE_GIF  => @imagecreatefromgif($source),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
            default        => false,
        };

        if (! $image instanceof GdImage) {
            return null;
        }

        $scale = min(1, self::THUMB_BOX / max(imagesx($image), imagesy($image)));
        $width = max(1, (int) round(imagesx($image) * $scale));
        $height = max(1, (int) round(imagesy($image) * $scale));

        $canvas = imagecreatetruecolor($width, $height);
        // JPEG has no transparency: transparent PNG/GIF/WebP pixels land on white.
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
        unset($image);

        // Phone photos are stored sideways with an EXIF rotation flag; GD ignores the flag.
        if ($info[2] === IMAGETYPE_JPEG) {
            $canvas = $this->applyExifOrientation($canvas, $source);
        }

        ob_start();
        imagejpeg($canvas, null, 82);
        $disk->put($thumb, (string) ob_get_clean());

        return $thumb;
    }

    /** GD holds ~5 bytes per pixel while decoding; skip what the memory limit can't take. */
    private function fitsInMemory(int $width, int $height): bool
    {
        $limit = ini_parse_quantity((string) ini_get('memory_limit') ?: '-1');
        $needed = $width * $height * 5;

        return $limit <= 0
            ? $needed <= 250 * 1024 * 1024
            : memory_get_usage() + $needed + 8 * 1024 * 1024 < $limit;
    }

    private function applyExifOrientation(GdImage $image, string $path): GdImage
    {
        $orientation = function_exists('exif_read_data')
            ? (int) (@exif_read_data($path)['Orientation'] ?? 1)
            : 1;

        return match ($orientation) {
            2       => $this->flipped($image, IMG_FLIP_HORIZONTAL),
            3       => $this->rotated($image, 180),
            4       => $this->flipped($image, IMG_FLIP_VERTICAL),
            5       => $this->flipped($this->rotated($image, 270), IMG_FLIP_HORIZONTAL),
            6       => $this->rotated($image, 270),
            7       => $this->flipped($this->rotated($image, 90), IMG_FLIP_HORIZONTAL),
            8       => $this->rotated($image, 90),
            default => $image,
        };
    }

    /** Counter-clockwise, as imagerotate() turns; keeps the image as is if GD fails. */
    private function rotated(GdImage $image, int $degrees): GdImage
    {
        $result = imagerotate($image, $degrees, 0);

        return $result instanceof GdImage ? $result : $image;
    }

    private function flipped(GdImage $image, int $mode): GdImage
    {
        imageflip($image, $mode);

        return $image;
    }
}
