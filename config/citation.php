<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Word limits
    |--------------------------------------------------------------------------
    | Change a number here and both the server-side validation and the live
    | word counters on the forms follow automatically.
    */
    'max_words' => [
        // Personal citation and Unit / Department citation
        'citation'       => 150,
        // "1 page executive summary" (roughly one page of single-spaced text)
        'report_summary' => 500,
        // Detailed annual report
        'report_detail'  => 3000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads (documents, video and audio)
    |--------------------------------------------------------------------------
    | Files go to a PRIVATE disk (storage/app/private by default), never to
    | public/. They are only reachable through the admin-only download route.
    |
    | Remember that the web server also has its own limits. For the sizes below
    | to work in production, raise these as well:
    |   - nginx:  client_max_body_size  (e.g. 120M)
    |   - php.ini: upload_max_filesize and post_max_size (e.g. 120M / 600M)
    */
    'uploads' => [
        'disk'        => env('CITATION_UPLOAD_DISK', 'local'),
        'directory'   => 'citation-attachments',
        'max_files'   => 5,
        'max_size_kb' => 102400, // 100 MB per file

        // Allowed extensions, grouped by the kind shown to admins
        'types' => [
            'document' => ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'odt', 'txt'],
            'video'    => ['mp4', 'mov', 'avi', 'webm', 'mkv', '3gp'],
            'audio'    => ['mp3', 'wav', 'm4a', 'aac', 'ogg', 'opus'],
        ],

        // The file's real content type (sniffed by the server, not the name)
        // must also be one of these. Wildcards are allowed.
        'mimetypes' => [
            'video/*',
            'audio/*',
            'application/ogg',
            'application/pdf',
            'application/msword',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.oasis.opendocument.text',
            'text/plain',
            // Office files are sometimes detected by their container format
            'application/zip',
            'application/x-ole-storage',
            'application/CDFV2',
        ],
    ],
];
