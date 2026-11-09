<?php

namespace App\Http\Controllers;

use App\Models\CitationAttachment;
use Illuminate\Support\Facades\Storage;

class CitationAttachmentController extends Controller
{
    /**
     * Download an uploaded file. Admin only (see routes/web.php).
     *
     * Files live on a private disk, so this is the only way to reach them.
     * They are always sent as a download, never rendered in the browser.
     */
    public function download(CitationAttachment $attachment)
    {
        $disk = Storage::disk($attachment->disk);

        abort_unless($disk->exists($attachment->path), 404, 'This file is no longer available.');

        return $disk->download($attachment->path, $attachment->original_name, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
