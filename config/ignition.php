<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Editor
    |--------------------------------------------------------------------------
    |
    | Choose your preferred editor to use when clicking an edit link
    | in the Ignition error page.
    |
    | Supported: "phpstorm", "vscode", "vscode-insiders", "textmate", "emacs",
    |            "sublime", "atom", "nova", "macvim", "idea", "netbeans",
    |            "xdebug"
    |
    */

    'editor' => env('IGNITION_EDITOR', 'vscode'),

    /*
    |--------------------------------------------------------------------------
    | Remote Sites Path Mapping
    |--------------------------------------------------------------------------
    |
    | If you are using a remote dev server, like Laravel Homestead, Docker, or
    | even a remote VPS, it will be necessary to map your remote paths to
    | your local machine for the editor to open the correct file.
    |
    */

    'remote_sites_path_mapping' => [
        // '/your-remote-path' => '/your-local-path'
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    |
    | Here you may specify which theme Ignition should use.
    |
    | Supported: "light", "dark", "auto"
    |
    */

    'theme' => env('IGNITION_THEME', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Enable Shareable Reports
    |--------------------------------------------------------------------------
    |
    | This option enables the "Share" button on the Ignition error page.
    | When clicked, it will create a shareable report that can be sent to
    | others, like in a Slack channel.
    |
    */

    'enable_shareable_report' => env('IGNITION_ENABLE_SHAREABLE_REPORT', true),

    /*
    |--------------------------------------------------------------------------
    | Enable runnable solutions
    |--------------------------------------------------------------------------
    |
    | This option enables the "Run solution" button on the Ignition error page.
    | When clicked, it will try to run the solution for the encountered error.
    |
    */

    'enable_runnable_solutions' => env('IGNITION_ENABLE_RUNNABLE_SOLUTIONS', false),

    /*
    |--------------------------------------------------------------------------
    | Ignore solutions
    |--------------------------------------------------------------------------
    |
    | Here you may specify which solutions should be ignored.
    |
    */

    'ignored_solutions' => [
        // App\Ignition\Solutions\CustomSolution::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable source code location
    |--------------------------------------------------------------------------
    |
    | This option enables the source code location on the Ignition error page.
    | When enabled, it will show the exact line of code that caused the error.
    |
    */

    'enable_source_code_location' => env('IGNITION_ENABLE_SOURCE_CODE_LOCATION', false),

    /*
    |--------------------------------------------------------------------------
    | Source code location line count
    |--------------------------------------------------------------------------
    |
    | Here you may specify how many lines of source code should be shown
    | around the line that caused the error.
    |
    */

    'source_code_location_line_count' => env('IGNITION_SOURCE_CODE_LOCATION_LINE_COUNT', 15),

    /*
    |--------------------------------------------------------------------------
    | Enable blade source mapping
    |--------------------------------------------------------------------------
    |
    | This option enables the blade source mapping on the Ignition error page.
    | When enabled, it will show the original blade template line that caused
    | the error. This can be disabled to prevent timeouts with large views.
    |
    */

    'enable_blade_source_mapping' => env('IGNITION_ENABLE_BLADE_SOURCE_MAPPING', false),

    /*
    |--------------------------------------------------------------------------
    | Log queries
    |--------------------------------------------------------------------------
    |
    | This option enables the logging of all database queries that are executed
    | during the request. This can be useful for debugging slow queries.
    |
    */

    'log_queries' => env('IGNITION_LOG_QUERIES', false),

    /*
    |--------------------------------------------------------------------------
    | Log queries slow threshold
    |--------------------------------------------------------------------------
    |
    | Here you may specify the threshold in milliseconds for a query to be
    | considered slow. Queries that take longer than this threshold will be
    | logged.
    |
    */

    'log_queries_slow_threshold' => env('IGNITION_LOG_QUERIES_SLOW_THRESHOLD', 100),

    /*
    |--------------------------------------------------------------------------
    | Detect duplicate queries
    |--------------------------------------------------------------------------
    |
    | This option enables the detection of duplicate queries that are executed
    | during the request. This can be useful for debugging performance issues.
    |
    */

    'detect_duplicate_queries' => env('IGNITION_DETECT_DUPLICATE_QUERIES', false),

    /*
    |--------------------------------------------------------------------------
    | Send logs to solutions
    |--------------------------------------------------------------------------
    |
    | This option enables the sending of logs to the solutions. This can be
    | useful for debugging issues that are not easily reproducible.
    |
    */

    'send_logs_to_solutions' => env('IGNITION_SEND_LOGS_TO_SOLUTIONS', false),
];
