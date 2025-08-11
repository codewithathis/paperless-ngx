<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paperless-ngx Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Paperless-ngx integration.
    | You can customize these settings based on your Paperless-ngx setup.
    |
    */

    // Base URL of your Paperless-ngx instance
    'base_url' => env('PAPERLESS_BASE_URL', 'http://localhost:8000'),

    // Authentication settings
    'auth' => [
        // Token-based authentication (recommended for API access)
        'token' => env('PAPERLESS_TOKEN', null),

        // Basic authentication (username/password)
        'username' => env('PAPERLESS_USERNAME', null),
        'password' => env('PAPERLESS_PASSWORD', null),

        // Authentication method priority:
        // 1. Token authentication (if token is provided)
        // 2. Basic authentication (if username and password are provided)
        'method' => env('PAPERLESS_AUTH_METHOD', 'token'), // 'token' or 'basic'
    ],

    // Default settings for document operations
    'defaults' => [
        'page_size' => env('PAPERLESS_PAGE_SIZE', 25),
        'timeout' => env('PAPERLESS_TIMEOUT', 30), // seconds
        'retry_attempts' => env('PAPERLESS_RETRY_ATTEMPTS', 3),
    ],

    // Document upload settings
    'upload' => [
        'max_file_size' => env('PAPERLESS_MAX_FILE_SIZE', 50 * 1024 * 1024), // 50MB
        'allowed_mime_types' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/tiff',
            'image/bmp',
            'image/gif',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
        ],
        'auto_ocr' => env('PAPERLESS_AUTO_OCR', true),
        'auto_tag' => env('PAPERLESS_AUTO_TAG', false),
    ],

    // Logging settings
    'logging' => [
        'enabled' => env('PAPERLESS_LOGGING_ENABLED', true),
        'level' => env('PAPERLESS_LOG_LEVEL', 'error'), // debug, info, warning, error
        'channel' => env('PAPERLESS_LOG_CHANNEL', 'paperless'),
    ],

    // Cache settings
    'cache' => [
        'enabled' => env('PAPERLESS_CACHE_ENABLED', true),
        'ttl' => env('PAPERLESS_CACHE_TTL', 3600), // seconds
        'prefix' => env('PAPERLESS_CACHE_PREFIX', 'paperless'),
    ],

    // Webhook settings (if you want to receive notifications from Paperless-ngx)
    'webhooks' => [
        'enabled' => env('PAPERLESS_WEBHOOKS_ENABLED', false),
        'secret' => env('PAPERLESS_WEBHOOK_SECRET', null),
        'endpoint' => env('PAPERLESS_WEBHOOK_ENDPOINT', '/api/webhooks/paperless'),
    ],

    // Custom field mappings (map your custom fields to specific purposes)
    'custom_fields' => [
        'invoice_number' => env('PAPERLESS_CUSTOM_FIELD_INVOICE_NUMBER', null),
        'due_date' => env('PAPERLESS_CUSTOM_FIELD_DUE_DATE', null),
        'amount' => env('PAPERLESS_CUSTOM_FIELD_AMOUNT', null),
        'vendor' => env('PAPERLESS_CUSTOM_FIELD_VENDOR', null),
        'category' => env('PAPERLESS_CUSTOM_FIELD_CATEGORY', null),
    ],

    // Document type mappings
    'document_types' => [
        'invoice' => env('PAPERLESS_DOCUMENT_TYPE_INVOICE', null),
        'receipt' => env('PAPERLESS_DOCUMENT_TYPE_RECEIPT', null),
        'contract' => env('PAPERLESS_DOCUMENT_TYPE_CONTRACT', null),
        'letter' => env('PAPERLESS_DOCUMENT_TYPE_LETTER', null),
        'report' => env('PAPERLESS_DOCUMENT_TYPE_REPORT', null),
    ],

    // Tag mappings
    'tags' => [
        'important' => env('PAPERLESS_TAG_IMPORTANT', null),
        'urgent' => env('PAPERLESS_TAG_URGENT', null),
        'archived' => env('PAPERLESS_TAG_ARCHIVED', null),
        'reviewed' => env('PAPERLESS_TAG_REVIEWED', null),
    ],

    // Storage path mappings
    'storage_paths' => [
        'invoices' => env('PAPERLESS_STORAGE_PATH_INVOICES', null),
        'receipts' => env('PAPERLESS_STORAGE_PATH_RECEIPTS', null),
        'contracts' => env('PAPERLESS_STORAGE_PATH_CONTRACTS', null),
        'correspondence' => env('PAPERLESS_STORAGE_PATH_CORRESPONDENCE', null),
    ],

    // Correspondent mappings
    'correspondents' => [
        'default' => env('PAPERLESS_CORRESPONDENT_DEFAULT', null),
    ],

    // Search settings
    'search' => [
        'default_limit' => env('PAPERLESS_SEARCH_LIMIT', 10),
        'enable_fuzzy_search' => env('PAPERLESS_FUZZY_SEARCH', true),
        'search_in_content' => env('PAPERLESS_SEARCH_IN_CONTENT', true),
    ],

    // Bulk operations settings
    'bulk_operations' => [
        'max_documents' => env('PAPERLESS_BULK_MAX_DOCUMENTS', 100),
        'timeout' => env('PAPERLESS_BULK_TIMEOUT', 300), // seconds
    ],

    // Email settings for document sharing
    'email' => [
        'from_address' => env('PAPERLESS_EMAIL_FROM', 'noreply@example.com'),
        'from_name' => env('PAPERLESS_EMAIL_FROM_NAME', 'Paperless System'),
        'subject_prefix' => env('PAPERLESS_EMAIL_SUBJECT_PREFIX', '[Paperless]'),
    ],

    // Security settings
    'security' => [
        'verify_ssl' => env('PAPERLESS_VERIFY_SSL', true),
        'allow_self_signed' => env('PAPERLESS_ALLOW_SELF_SIGNED', false),
        'timeout' => env('PAPERLESS_REQUEST_TIMEOUT', 30),
    ],
];
