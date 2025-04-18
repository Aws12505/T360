<?php

return [

    /*
     |----------------------------------------------------------------------
     | Debugbar Settings
     |----------------------------------------------------------------------
     |
     | Debugbar is enabled by default when debug is set to true in app.php.
     | You can override the value by setting enable to true or false instead of null.
     |
     */

    'enabled' => env('APP_DEBUG', true),  // Force enabled when APP_DEBUG is true in .env
    'hide_empty_tabs' => true,  // Hide tabs until they have content
    'except' => [
        'telescope*',
        'horizon*',
    ],

    /*
     |----------------------------------------------------------------------
     | Storage settings
     |----------------------------------------------------------------------
     |
     | Debugbar stores data for session/ajax requests.
     | By default, file storage (in the storage folder) is used.
     |
     */
    'storage' => [
        'enabled'    => true,  // Enable storage
        'open'       => env('DEBUGBAR_OPEN_STORAGE', false), // Make this false or use your own callback for security
        'driver'     => 'file',  // Use file storage
        'path'       => storage_path('debugbar'),  // Directory to store files
    ],

    /*
     |----------------------------------------------------------------------
     | DataCollectors
     |----------------------------------------------------------------------
     |
     | Enable/disable DataCollectors
     |
     */
    'collectors' => [
        'phpinfo'         => false,  // Php version info
        'messages'        => true,   // Messages
        'time'            => true,   // Time Datalogger
        'memory'          => true,   // Memory usage
        'exceptions'      => true,   // Exception displayer
        'log'             => true,   // Logs from Monolog
        'db'              => true,   // Database queries
        'views'           => true,   // Views and their data
        'route'           => false,  // Disable route collector (if needed)
        'auth'            => false,  // Disable authentication details
        'gate'            => true,   // Gate checks
        'session'         => false,  // Session data
        'symfony_request' => true,   // Symfony request data
        'mail'            => true,   // Email messages
        'laravel'         => true,   // Laravel version info
        'events'          => false,  // Event firing info
        'logs'            => false,  // Latest log messages
        'files'           => false,  // Show included files
        'config'          => false,  // Config settings
        'cache'           => false,  // Cache events
        'models'          => true,   // Models info
        'livewire'        => true,   // Livewire data (if available)
        'jobs'            => false,  // Dispatched jobs
        'pennant'         => false,  // Feature flags
    ],

    /*
     |----------------------------------------------------------------------
     | AJAX Request Capture
     |----------------------------------------------------------------------
     |
     | Capture Ajax requests and display them in the Debugbar
     */
    'capture_ajax' => true,  // Enable capturing of AJAX requests
    'add_ajax_timing' => false,  // Disables Ajax timing
    'ajax_handler_auto_show' => true,  // Automatically show Ajax data
    'ajax_handler_enable_tab' => true,  // Enable tab for Ajax requests
    'defer_datasets' => false,  // Don't defer loading of Ajax data

    /*
     |----------------------------------------------------------------------
     | Debugbar Theme
     |----------------------------------------------------------------------
     |
     | Choose between light or dark themes, or let it auto-detect
     | based on system preferences.
     */
    'theme' => env('DEBUGBAR_THEME', 'auto'),

    /*
     |----------------------------------------------------------------------
     | Editor configuration
     |----------------------------------------------------------------------
     |
     | Choose your preferred editor to use when clicking file names in Debugbar.
     */
    'editor' => env('DEBUGBAR_EDITOR', 'phpstorm'),

    /*
     |----------------------------------------------------------------------
     | Remote Path Mapping for Debugbar
     |----------------------------------------------------------------------
     |
     | Configure remote server paths for Debugbar when using tools like Docker, Vagrant, etc.
     */
    'remote_sites_path' => env('DEBUGBAR_REMOTE_SITES_PATH', ''),
    'local_sites_path' => env('DEBUGBAR_LOCAL_SITES_PATH', ''),

    /*
     |----------------------------------------------------------------------
     | Vendors
     |----------------------------------------------------------------------
     |
     | Enable vendor files like font-awesome and jQuery in Debugbar.
     */
    'include_vendors' => true,

    /*
     |----------------------------------------------------------------------
     | Debugbar route prefix
     |----------------------------------------------------------------------
     |
     | You can set the route prefix for Debugbar assets.
     */
    'route_prefix' => '_debugbar',

    /*
     |----------------------------------------------------------------------
     | Debugbar route domain
     |----------------------------------------------------------------------
     |
     | Override the default domain for Debugbar route
     */
    'route_domain' => null,

    /*
     |----------------------------------------------------------------------
     | Debugbar Injecting into Responses
     |----------------------------------------------------------------------
     |
     | Whether Debugbar is injected automatically into the response.
     */
    'inject' => true,

    /*
     |----------------------------------------------------------------------
     | Backtrace stack limit
     |----------------------------------------------------------------------
     |
     | Limit the number of backtrace frames displayed by Debugbar
     */
    'debug_backtrace_limit' => 50,
];
