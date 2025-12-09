<?php

return [
    'pdf' => [
        'enabled' => true,
        // Automatically quote the binary to handle Windows paths with spaces.
        'binary' => sprintf(
            '"%s"',
            trim(env('WKHTMLTOPDF_BINARY', '/vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64'), '"')
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
            trim(env('WKHTMLTOIMAGE_BINARY', '/vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64'), '"')
        ),
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
        ],
        'env' => [],
    ],
];
