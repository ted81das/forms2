<?php

return [

    'asset_version' => 50,

    'select2_asset' => '<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" /><script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/js/select2.min.js"></script>',

    'doc_path' => 'form',
    'langs' => [
        'en' => ['full_name' => 'English', 'short_name' => 'English'],
        'es' => ['full_name' => 'Spanish - Español', 'short_name' => 'Spanish'],
        // 'sq' => ['full_name' => 'Albanian - Shqip', 'short_name' => 'Albanian'],
        // 'hi' => ['full_name' => 'Hindi - हिंदी', 'short_name' => 'Hindi'],
        'nl' => ['full_name' => 'Dutch', 'short_name' => 'Dutch'],
        'fr' => ['full_name' => 'French - Français', 'short_name' => 'French'],
        'de' => ['full_name' => 'German - Deutsch', 'short_name' => 'German'],
        // 'ar' => ['full_name' => 'Arabic - العَرَبِيَّة', 'short_name' => 'Arabic'],
        // 'tr' => ['full_name' => 'Turkish - Türkçe', 'short_name' => 'Turkish'],
    ],
    'langs_rtl' => ['ar'],
    'non_utf8_languages' => ['ar', 'hi'],
    'STRIPE_PUB_KEY' => env('STRIPE_PUB_KEY'),
    'STRIPE_SECRET_KEY' => env('STRIPE_SECRET_KEY'),
    'APP_DATE_FORMAT' => env('APP_DATE_FORMAT'),
    'APP_TIME_FORMAT' => env('APP_TIME_FORMAT'),
    'superadmins_emails' => env('SUPERADMIN_EMAILS'),
    'ENABLE_OFFLINE_PAYMENT' => env('ENABLE_OFFLINE_PAYMENT', 0),
    'ACELLE_MAIL_NAME' => env('ACELLE_MAIL_NAME', ''),
    'ACELLE_MAIL_API' => env('ACELLE_MAIL_API', ''),
    'max_upload_size' => 1, //In MB
    'pdf_upload_size' => 5,
];
