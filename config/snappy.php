<?php

return [
    'pdf' => [
        'enabled' => true,
        // Automatically quote the binary to handle Windows paths with spaces.
        'binary' => sprintf(
            '"%s"',
            trim(env('WKHTMLTOPDF_BINARY', 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe'), '"')
        ),
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
        ],
        'env' => [],
    ],

    'image' => [
        'enabled' => true,
        // Automatically quote the binary to handle Windows paths with spaces.
        'binary' => sprintf(
            '"%s"',
            trim(env('WKHTMLTOIMAGE_BINARY', 'C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe'), '"')
        ),
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
        ],
        'env' => [],
    ],
];
