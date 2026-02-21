<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesImages
{
    /**
     * Store an uploaded image, converting it to WebP format.
     *
     * @param  UploadedFile  $file      The uploaded image file
     * @param  string        $directory Storage subdirectory (e.g. 'avatars', 'contests')
     * @param  int           $maxWidth  Max width to resize to (0 = no resize)
     * @param  int           $quality   WebP quality (1-100)
     * @return string|null   Relative storage path to the saved file, or null on failure
     */
    protected function storeImageAsWebp(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 0,
        int $quality = 85,
    ): ?string {
        $image = $this->createGdImage($file);

        if ($image === null) {
            return null;
        }

        if ($maxWidth > 0) {
            $image = $this->resizeImage($image, $maxWidth);
        }

        $filename = Str::ulid() . '.webp';
        $relativePath = $directory . '/' . $filename;

        $tempPath = tempnam(sys_get_temp_dir(), 'webp');
        imagewebp($image, $tempPath, $quality);
        imagedestroy($image);

        Storage::disk('public')->put($relativePath, file_get_contents($tempPath));
        @unlink($tempPath);

        return $relativePath;
    }

    /**
     * Delete a previously stored image.
     */
    protected function deleteStoredImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Create a GD image resource from an uploaded file.
     */
    private function createGdImage(UploadedFile $file): ?\GdImage
    {
        $mime = $file->getMimeType();
        $path = $file->getPathname();

        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path) ?: null,
            'image/png' => $this->createFromPngWithAlpha($path),
            'image/gif' => @imagecreatefromgif($path) ?: null,
            'image/webp' => @imagecreatefromwebp($path) ?: null,
            'image/bmp' => @imagecreatefrombmp($path) ?: null,
            default => null,
        };
    }

    /**
     * Handle PNG with alpha channel preservation.
     */
    private function createFromPngWithAlpha(string $path): ?\GdImage
    {
        $image = @imagecreatefrompng($path);

        if (! $image) {
            return null;
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * Resize an image proportionally to fit within maxWidth.
     */
    private function resizeImage(\GdImage $image, int $maxWidth): \GdImage
    {
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        if ($origWidth <= $maxWidth) {
            return $image;
        }

        $ratio = $maxWidth / $origWidth;
        $newHeight = (int) round($origHeight * $ratio);

        $resized = imagecreatetruecolor($maxWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $origWidth, $origHeight);
        imagedestroy($image);

        return $resized;
    }
}
