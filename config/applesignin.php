<?php
return [
    //credentials for authorization
    'client_id' => env('APPLE_CLIENT_ID'),
    'redirect' => env('APPLE_REDIRECT_URI'),
    'key_id' => env('APPLE_KEY_ID'),
    'team_id' => env('APPLE_TEAM_ID'),
    'private_key' => env('APPLE_PRIVATE_KEY'),

    // users table columns and model
    'user_model' => \App\Models\User::class,

    'user_columns' => [
        'email' => 'email',
        'fullname' => 'fullname',
        'username' => 'username',
    ],

    // Social account table columns and model
    'social_account_model' => \App\Models\SocialAccount::class,

    'social_columns' => [
        'provider_user_id' => 'provider_user_id',
        'provider' => 'provider',
        'user_id' => 'user_id',
    ],
];