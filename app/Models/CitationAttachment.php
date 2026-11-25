<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

/**
 * A document or spreadsheet uploaded with a citation submission.
 * Stored on a private disk; only admins can download it.
 */
class CitationAttachment extends Model
{
    protected $fillable = [
        'kind',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
    ];

    protected static function booted(): void
    {
        // Keep the disk tidy: removing the record removes the file.
        static::deleting(function (self $attachment) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        });
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Remove only the database row (the file stays on disk). Used while editing,
     * so the file is deleted only after the whole edit has been saved.
     */
    public function deleteRecordOnly(): void
    {
        static::whereKey($this->getKey())->delete();
    }

    /**
     * Save an uploaded file to the private disk and record it for $owner.
     */
    public static function storeFor(Model $owner, UploadedFile $file): self
    {
        $disk      = config('citation.uploads.disk');
        $extension = strtolower($file->getClientOriginalExtension());

        // Random name on disk: the visitor's file name is never used as a path.
        $path = $file->storeAs(
            config('citation.uploads.directory') . '/' . now()->format('Y/m'),
            Str::uuid() . '.' . $extension,
            $disk
        );

        return $owner->attachments()->create([
            'kind'          => self::kindFor($extension),
            'original_name' => Str::limit($file->getClientOriginalName(), 200, ''),
            'path'          => $path,
            'disk'          => $disk,
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
        ]);
    }

    public static function kindFor(string $extension): string
    {
        foreach (config('citation.uploads.types') as $kind => $extensions) {
            if (in_array(strtolower($extension), $extensions, true)) {
                return $kind;
            }
        }

        return 'document';
    }

    public function humanSize(): string
    {
        return Number::fileSize((int) $this->size, precision: 1);
    }
}
