<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CitationAttachment;
use App\Rules\MaxWords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Rules and file handling shared by the department and group submission forms:
 *   - personal annual report (executive summary + detailed report)
 *   - unit / department annual report (executive summary + detailed report)
 *   - unit / department citation
 *   - optional document, video and audio uploads
 *
 * All limits come from config/citation.php.
 */
trait HandlesCitationSubmission
{
    /** Rule for a citation field (personal or unit): required, max 150 words. */
    protected function citationRules(): array
    {
        return ['required', 'string', new MaxWords(config('citation.max_words.citation'))];
    }

    /** Validation rules for everything the new form fields add. */
    protected function submissionRules(): array
    {
        $words   = config('citation.max_words');
        $uploads = config('citation.uploads');

        $extensions = collect($uploads['types'])->flatten()->implode(',');
        $mimetypes  = implode(',', $uploads['mimetypes']);

        // The character caps are a safety net so a "word" cannot be 50,000
        // characters long. They sit far above what the word limits allow.
        return [
            'personal_report_summary' => ['required', 'string', 'max:10000', new MaxWords($words['report_summary'])],
            'personal_report_detail'  => ['required', 'string', 'max:30000', new MaxWords($words['report_detail'])],
            'unit_report_summary'     => ['required', 'string', 'max:10000', new MaxWords($words['report_summary'])],
            'unit_report_detail'      => ['required', 'string', 'max:30000', new MaxWords($words['report_detail'])],
            'unit_citation'           => $this->citationRules(),

            'attachments'   => ['nullable', 'array', 'max:' . $uploads['max_files']],
            'attachments.*' => [
                'file',
                'extensions:' . $extensions,
                'mimetypes:' . $mimetypes,
                'max:' . $uploads['max_size_kb'],
            ],
        ];
    }

    protected function submissionMessages(): array
    {
        $uploads = config('citation.uploads');
        $maxMb   = (int) round($uploads['max_size_kb'] / 1024);

        return [
            'attachments.max'            => "You can upload up to {$uploads['max_files']} files.",
            'attachments.*.uploaded'     => 'One of the files could not be uploaded. It may be larger than the server allows, so please try a smaller file.',
            'attachments.*.max'          => "Each file must be {$maxMb} MB or smaller.",
            'attachments.*.extensions'   => 'Only document (PDF, Word, PowerPoint), video and audio files can be uploaded.',
            'attachments.*.mimetypes'    => 'Only document (PDF, Word, PowerPoint), video and audio files can be uploaded.',
        ];
    }

    protected function submissionAttributes(): array
    {
        return [
            'personal_report_summary' => 'personal report executive summary',
            'personal_report_detail'  => 'personal detailed report',
            'unit_report_summary'     => 'unit / department report executive summary',
            'unit_report_detail'      => 'unit / department detailed report',
            'unit_citation'           => 'unit / department citation',
            'attachments.*'           => 'file',
        ];
    }

    /**
     * Create the submission and save its uploaded files together.
     * If anything fails, the database rolls back and any files already
     * written are removed, so no orphans are left behind.
     *
     * @param  class-string<Model>  $model
     */
    protected function createWithAttachments(string $model, array $attributes, Request $request): Model
    {
        $stored = [];

        try {
            return DB::transaction(function () use ($model, $attributes, $request, &$stored) {
                $citation = $model::create($attributes);

                foreach ($request->file('attachments', []) as $file) {
                    $attachment = CitationAttachment::storeFor($citation, $file);
                    $stored[]   = [$attachment->disk, $attachment->path];
                }

                return $citation;
            });
        } catch (Throwable $e) {
            foreach ($stored as [$disk, $path]) {
                Storage::disk($disk)->delete($path);
            }

            throw $e;
        }
    }
}
