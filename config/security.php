<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Data Encryption Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for advanced data encryption features including
    | field-level encryption and key management.
    |
    */
    'encryption' => [
        'algorithm' => 'AES-256-GCM',
        'key_rotation_days' => 90,
        'encrypted_fields' => [
            'users' => ['whatsapp_number'],
            'tasks' => ['title', 'description'],
            'notes' => ['title', 'content'],
            'routines' => ['title', 'description'],
            'reminders' => ['title', 'description'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection Configuration
    |--------------------------------------------------------------------------
    |
    | Enhanced CSRF protection settings including double-submit cookie
    | pattern and SameSite cookie attributes.
    |
    */
    'csrf' => [
        'double_submit_enabled' => true,
        'cookie_name' => 'XSRF-TOKEN',
        'header_name' => 'X-XSRF-TOKEN',
        'same_site' => 'strict',
        'secure' => env('APP_ENV') === 'production',
        'http_only' => false, // Must be false for JavaScript access
        'lifetime' => 120, // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for security headers to be added to all responses.
    |
    */
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self'",
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Audit Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for security auditing and logging.
    |
    */
    'audit' => [
        'enabled' => true,
        'log_channel' => 'security',
        'events' => [
            'login_attempts',
            'password_changes',
            'data_access',
            'data_modifications',
            'failed_authentications',
            'privilege_escalations',
        ],
        'retention_days' => 365,
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security Configuration
    |--------------------------------------------------------------------------
    |
    | Enhanced session security settings.
    |
    */
    'session' => [
        'regenerate_on_login' => true,
        'invalidate_on_logout' => true,
        'timeout_minutes' => 120,
        'concurrent_sessions' => 1,
        'ip_validation' => true,
        'user_agent_validation' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for API and form submission rate limiting.
    |
    */
    'rate_limiting' => [
        'login_attempts' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
        ],
        'api_requests' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
        ],
        'form_submissions' => [
            'max_attempts' => 10,
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Validation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for enhanced input validation and sanitization.
    |
    */
    'validation' => [
        'strict_mode' => true,
        'sanitize_html' => true,
        'max_input_length' => 10000,
        'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'max_file_size' => 5120, // KB
    ],
];
