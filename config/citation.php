<?php

return [

    /*
    |--------------------------------------------------------------------------
    | The four things people can submit
    |--------------------------------------------------------------------------
    | key        stored in the database (reports.type)
    | slug       used in the public URL (/submit/{slug})
    | kind       "report" (executive summary + detailed report) or "citation"
    | scope      "personal" or "unit"
    */
    'reports' => [
        'personal_report' => [
            'slug'        => 'personal-report',
            'kind'        => 'report',
            'scope'       => 'personal',
            'label'       => 'Personal annual report',
            'short'       => 'Personal report',
            'description' => 'Ideas and innovations you executed, and your significant contributions to the zone and the ministry.',
            'includes'    => ['1-page executive summary', 'Detailed report'],
        ],
        'unit_report' => [
            'slug'        => 'unit-report',
            'kind'        => 'report',
            'scope'       => 'unit',
            'label'       => 'Unit, sub-department or department annual report',
            'short'       => 'Unit report',
            'description' => 'The annual report for your unit, sub-department or department.',
            'includes'    => ['1-page executive summary', 'Detailed report'],
        ],
        'personal_citation' => [
            'slug'        => 'personal-citation',
            'kind'        => 'citation',
            'scope'       => 'personal',
            'label'       => 'Personal citation',
            'short'       => 'Personal citation',
            'description' => 'A short citation recognising you and the work you did this year.',
            'includes'    => ['Not more than 150 words'],
        ],
        'unit_citation' => [
            'slug'        => 'unit-citation',
            'kind'        => 'citation',
            'scope'       => 'unit',
            'label'       => 'Unit or department citation',
            'short'       => 'Unit citation',
            'description' => 'A short citation recognising your unit or department and its work this year.',
            'includes'    => ['Not more than 150 words'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Word limits
    |--------------------------------------------------------------------------
    | Change a number here and both the server-side validation and the live
    | word counters on the forms follow automatically.
    */
    'max_words' => [
        'citation'       => 150,
        // "1 page executive summary" (roughly one page of single-spaced text)
        'report_summary' => 500,
        'report_detail'  => 3000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads (documents, spreadsheets, video and audio)
    |--------------------------------------------------------------------------
    | Files go to a PRIVATE disk (storage/app/private by default), never to
    | public/. They are only reachable through the admin-only download route.
    |
    | The web server has its own limits too. For these sizes to work in
    | production, raise them as well:
    |   - nginx:   client_max_body_size  (e.g. 120M)
    |   - php.ini: upload_max_filesize and post_max_size (e.g. 100M / 600M)
    */
    'uploads' => [
        'disk'        => env('CITATION_UPLOAD_DISK', 'local'),
        'directory'   => 'citation-attachments',
        'max_files'   => 5,
        'max_size_kb' => 102400, // 100 MB per file

        // Allowed extensions, grouped by the kind shown to admins
        'types' => [
            'document' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'odt', 'txt'],
            'sheet'    => ['xls', 'xlsx', 'csv', 'ods'],
            'video'    => ['mp4', 'mov', 'avi', 'webm', 'mkv', '3gp'],
            'audio'    => ['mp3', 'wav', 'm4a', 'aac', 'ogg', 'opus'],
        ],

        // The file's real content type (sniffed by the server, not its name)
        // must also be one of these. Wildcards are allowed.
        'mimetypes' => [
            'video/*',
            'audio/*',
            'application/ogg',
            'application/pdf',
            'application/msword',
            'application/vnd.ms-powerpoint',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet',
            'text/plain',
            'text/csv',
            'application/csv',
            // Office files are sometimes detected by their container format
            'application/zip',
            'application/x-ole-storage',
            'application/CDFV2',
        ],
    ],
];
