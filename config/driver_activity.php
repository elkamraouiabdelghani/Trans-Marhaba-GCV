<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver Activity Compliance Rules
    |--------------------------------------------------------------------------
    |
    | These settings define the compliance rules for driver activities,
    | including maximum driving hours, minimum rest hours, and working
    | window restrictions.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Daily Work Hours Structure
    |--------------------------------------------------------------------------
    |
    | Drivers work 12 hours per day total:
    | - 8 hours for driving
    | - 4 hours for rest
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Maximum Daily Driving Hours
    |--------------------------------------------------------------------------
    |
    | The maximum number of hours a driver can drive in a single day.
    | Standard: 8 hours per day.
    | This is used to check compliance violations.
    |
    */

    'max_daily_driving_hours' => env('DRIVER_MAX_DAILY_DRIVING_HOURS', 8),

    /*
    |--------------------------------------------------------------------------
    | Minimum Daily Rest Hours
    |--------------------------------------------------------------------------
    |
    | The minimum number of hours a driver must rest in a single day.
    | Standard: 4 hours per day.
    | This is used to check compliance violations.
    |
    */

    'min_daily_rest_hours' => env('DRIVER_MIN_DAILY_REST_HOURS', 4),

    /*
    |--------------------------------------------------------------------------
    | Maximum Total Daily Work Hours
    |--------------------------------------------------------------------------
    |
    | The maximum total work hours per day (driving + rest).
    | Standard: 12 hours per day (8 driving + 4 rest).
    | This is used to check compliance violations.
    |
    */

    'max_daily_total_hours' => env('DRIVER_MAX_DAILY_TOTAL_HOURS', 12),

    /*
    |--------------------------------------------------------------------------
    | Allowed Working Window
    |--------------------------------------------------------------------------
    |
    | The allowed time window for driver activities. Activities starting
    | before the start time or ending after the end time will be flagged
    | as non-compliant.
    |
    | Format: 'HH:MM' (24-hour format)
    |
    */

    'working_window_start' => env('DRIVER_WORKING_WINDOW_START', '06:00'),
    'working_window_end' => env('DRIVER_WORKING_WINDOW_END', '20:00'),

    /*
    |--------------------------------------------------------------------------
    | Compliance Violation Severity Thresholds
    |--------------------------------------------------------------------------
    |
    | These thresholds determine the severity of compliance violations:
    | - low: Minor violations (e.g., 1-2 hours over limit)
    | - medium: Moderate violations (e.g., 2-4 hours over limit)
    | - high: Severe violations (e.g., 4+ hours over limit)
    |
    */

    'violation_thresholds' => [
        'driving_hours' => [
            'low' => 1,      // 1 hour over limit
            'medium' => 2,   // 2 hours over limit
            'high' => 4,     // 4+ hours over limit
        ],
        'rest_hours' => [
            'low' => 1,      // 1 hour under minimum
            'medium' => 2,   // 2 hours under minimum
            'high' => 3,     // 3+ hours under minimum
        ],
        'total_hours' => [
            'low' => 1,      // 1 hour over 12-hour limit
            'medium' => 2,   // 2 hours over 12-hour limit
            'high' => 3,     // 3+ hours over 12-hour limit
        ],
        'working_window' => [
            'low' => 30,     // 30 minutes outside window
            'medium' => 60,  // 1 hour outside window
            'high' => 120,   // 2+ hours outside window
        ],
    ],

];

