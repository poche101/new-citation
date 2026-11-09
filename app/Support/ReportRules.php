<?php

namespace App\Support;

use App\Models\Report;
use App\Rules\MaxWords;

/**
 * Validation shared by the public submission form and the admin edit page,
 * so both enforce exactly the same limits (all set in config/citation.php).
 */
class ReportRules
{
    /** @return array<string, mixed> */
    public static function for(string $type): array
    {
        $words = config('citation.max_words');
        $kind  = Report::typeConfig($type)['kind'] ?? 'citation';

        $rules = [
            'title'       => ['nullable', 'string', 'max:255'],
            'name'        => ['required', 'string', 'max:255'],
            'unit'        => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'kingschat'   => ['required', 'string', 'max:255'],
            'phone'       => ['required', 'string', 'max:30'],

            'period_from' => ['required', 'date'],
            'period_to'   => ['required', 'date', 'after_or_equal:period_from'],
        ];

        if ($kind === 'report') {
            // The character caps are a safety net so a single "word" cannot be
            // 50,000 characters long. They sit far above what the word limits allow.
            $rules['executive_summary'] = ['required', 'string', 'max:10000', new MaxWords($words['report_summary'])];
            $rules['detailed_report']   = ['required', 'string', 'max:30000', new MaxWords($words['report_detail'])];
        } else {
            $rules['citation'] = ['required', 'string', 'max:3000', new MaxWords($words['citation'])];
        }

        return $rules;
    }

    /** Rules for uploaded files ($alreadyAttached counts files an admin keeps when editing). */
    public static function attachments(int $alreadyAttached = 0): array
    {
        $uploads    = config('citation.uploads');
        $extensions = collect($uploads['types'])->flatten()->implode(',');

        return [
            'attachments'   => ['nullable', 'array', 'max:' . max(0, $uploads['max_files'] - $alreadyAttached)],
            'attachments.*' => [
                'file',
                'extensions:' . $extensions,
                'mimetypes:' . implode(',', $uploads['mimetypes']),
                'max:' . $uploads['max_size_kb'],
            ],
        ];
    }

    public static function messages(): array
    {
        $uploads = config('citation.uploads');
        $maxMb   = (int) round($uploads['max_size_kb'] / 1024);
        $typeMsg = 'Only documents (PDF, Word, PowerPoint), spreadsheets (Excel, CSV), video and audio files can be uploaded.';

        return [
            'period_to.after_or_equal' => 'The "To" date must be the same as or later than the "From" date.',
            'attachments.max'          => "You can attach up to {$uploads['max_files']} files in total.",
            'attachments.*.uploaded'   => 'A file could not be uploaded. It may be larger than the server allows, so try a smaller file.',
            'attachments.*.max'        => "Each file must be {$maxMb} MB or smaller.",
            'attachments.*.extensions' => $typeMsg,
            'attachments.*.mimetypes'  => $typeMsg,
        ];
    }

    public static function attributes(): array
    {
        return [
            'name'              => 'full name',
            'kingschat'         => 'Kingschat handle',
            'phone'             => 'phone number',
            'period_from'       => '"From" date',
            'period_to'         => '"To" date',
            'executive_summary' => 'executive summary',
            'detailed_report'   => 'detailed report',
            'attachments.*'     => 'file',
        ];
    }
}
