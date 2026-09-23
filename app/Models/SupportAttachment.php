<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\SupportAttachmentService;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SupportAttachment extends Model
{
    use HasUlids;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'token',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    /** Only `token` is a ULID — the primary key stays auto-increment. */
    public function uniqueIds(): array
    {
        return ['token'];
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function downloadUrl(): string
    {
        return route('support-attachments.download', $this->token);
    }

    /** Full-size inline view, used by the lightbox. */
    public function previewUrl(): string
    {
        return route('support-attachments.preview', $this->token);
    }

    public function thumbnailUrl(): string
    {
        return route('support-attachments.thumb', $this->token);
    }

    public function humanSize(): string
    {
        return SupportAttachmentService::formatBytes($this->size_bytes);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /** Checked against the sniffed type, so a renamed file never shows inline. */
    public function isPreviewable(): bool
    {
        return in_array($this->mime_type, SupportAttachmentService::PREVIEWABLE_MIMES, true);
    }

    public function iconClass(): string
    {
        return match (strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION))) {
            'pdf'         => 'fa-file-pdf',
            'doc', 'docx' => 'fa-file-word',
            'xls', 'xlsx' => 'fa-file-excel',
            'zip'         => 'fa-file-zipper',
            'txt'         => 'fa-file-lines',
            default       => $this->isImage() ? 'fa-file-image' : 'fa-file',
        };
    }
}
