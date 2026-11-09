<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CitationAttachment;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Saving a report together with its uploaded files.
 * If anything fails, the database rolls back and any file already written is
 * removed, so no orphan files are left on the disk.
 */
trait ManagesReportFiles
{
    protected function createReportWithFiles(array $attributes, Request $request): Report
    {
        return $this->withFileCleanup(function (array &$stored) use ($attributes, $request) {
            $report = Report::create($attributes);
            $this->storeFiles($report, $request, $stored);

            return $report;
        });
    }

    protected function updateReportWithFiles(Report $report, array $attributes, array $removeIds, Request $request): Report
    {
        $filesToDelete = [];

        $report = $this->withFileCleanup(function (array &$stored) use ($report, $attributes, $removeIds, $request, &$filesToDelete) {
            $report->update($attributes);

            // Only files that belong to this report can be removed
            $filesToDelete = $report->attachments()->whereIn('id', $removeIds)->get();
            $filesToDelete->each(fn ($a) => $a->deleteRecordOnly());

            $this->storeFiles($report, $request, $stored);

            return $report;
        });

        // Remove old files from the disk only after everything else succeeded
        collect($filesToDelete)->each(fn ($a) => Storage::disk($a->disk)->delete($a->path));

        return $report;
    }

    private function storeFiles(Report $report, Request $request, array &$stored): void
    {
        foreach ($request->file('attachments', []) as $file) {
            $attachment = CitationAttachment::storeFor($report, $file);
            $stored[]   = [$attachment->disk, $attachment->path];
        }
    }

    private function withFileCleanup(callable $work): Report
    {
        $stored = [];

        try {
            return DB::transaction(fn () => $work($stored));
        } catch (Throwable $e) {
            foreach ($stored as [$disk, $path]) {
                Storage::disk($disk)->delete($path);
            }

            throw $e;
        }
    }
}
