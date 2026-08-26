<?php

return [
    'provider' => env('VIDEO_AD_AGENT_PROVIDER', 'openai'), // openai, anthropic, mock

    'openai_api_key' => env('OPENAI_API_KEY'),
    'openai_model' => env('OPENAI_MODEL', 'gpt-4o'),

    'anthropic_api_key' => env('ANTHROPIC_API_KEY'),
    'anthropic_model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),

    // Default settings
    'default_tone' => 'professional',
    'default_duration' => 30,
    'default_platform' => 'social_media',
];