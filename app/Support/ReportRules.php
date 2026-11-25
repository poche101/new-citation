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

        // Annual reports are submitted as an uploaded file; only citations are typed.
        if ($kind === 'citation') {
            // The character cap is a safety net so a single "word" cannot be
            // thousands of characters long. It sits far above what the word limit allows.
            $rules['citation'] = ['required', 'string', 'max:3000', new MaxWords($words['citation'])];
        }

        return $rules;
    }

    /** Rules for uploaded files ($alreadyAttached counts files an admin keeps when editing). */
    public static function attachments(int $alreadyAttached = 0, bool $required = false): array
    {
        $uploads    = config('citation.uploads');
        $extensions = collect($uploads['types'])->flatten()->implode(',');

        return [
            'attachments'   => array_merge(
                $required ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
                ['max:' . max(0, $uploads['max_files'] - $alreadyAttached)]
            ),
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
        $typeMsg = 'Only documents (PDF, Word, PowerPoint), spreadsheets (Excel, CSV) can be uploaded.';

        return [
            'period_to.after_or_equal' => 'The "To" date must be the same as or later than the "From" date.',
            'attachments.required'     => 'Please attach your report as a document or spreadsheet.',
            'attachments.min'          => 'Please attach your report as a document or spreadsheet.',
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
            'attachments.*'     => 'file',
        ];
    }
}
