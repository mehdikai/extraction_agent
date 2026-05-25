<?php

return [

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'vision_llm' => [
        'model' => env('VISION_LLM_MODEL', 'moonshotai/kimi-k2.5'),
        'url'   => env('VISION_LLM_URL', 'https://integrate.api.nvidia.com/v1/chat/completions'),
    ],

    'nvidia' => [
        'key' => env('NVIDIA_API_KEY'),
    ],

];