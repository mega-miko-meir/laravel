<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'weather' => [
        'key' => env('WEATHER_API_KEY'),
    ],

    'gemini' => [
        'key'   => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash-lite'),
    ],

    'anthropic' => [
        'key'   => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),
    ],

    'grok' => [
        'key'   => env('GROK_API_KEY'),
        'model' => env('GROK_MODEL', 'grok-4-fast'),
    ],

    // Учётка для автоматического входа на nobel.provisor.kz (интеграция «КМП»,
    // см. scripts/kmp_etl.py) + креды nobeldb с правом записи (КМП грузится
    // в таблицу KMP_data — обычный DB_* коннекшен 'nobel' там read-only).
    'kmp_portal' => [
        'username'               => env('KMP_PORTAL_USERNAME'),
        'password'               => env('KMP_PORTAL_PASSWORD'),
        'nobeldb_write_user'     => env('KMP_NOBELDB_USER'),
        'nobeldb_write_password' => env('KMP_NOBELDB_PASS'),
        // Необязательно: личный файл "Недели поставщика.xlsx", в репозиторий
        // не кладём — см. scripts/kmp_etl.py.
        'sw_reference_file'      => env('KMP_SW_REFERENCE_FILE'),
    ],

];
