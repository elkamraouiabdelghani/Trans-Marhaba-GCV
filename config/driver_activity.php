<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver Activity Compliance Rules
    |--------------------------------------------------------------------------
    |
    | These settings define the compliance rules for driver activities,
    | including maximum driving hours, rest hours, and total working time.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Daily Work Hours Structure
    |--------------------------------------------------------------------------
    |
    | Drivers work 12 hours per day total:
    | - 9 hours maximum of driving
    | - 3 hours maximum of rest
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

    'max_daily_driving_hours' => env('DRIVER_MAX_DAILY_DRIVING_HOURS', 9),

    /*
    |--------------------------------------------------------------------------
    | Maximum Daily Rest Hours
    |--------------------------------------------------------------------------
    |
    | The maximum number of hours a driver may rest in a single day.
    | Standard: 3 hours per day.
    | This is used to check compliance violations.
    |
    */

    'max_daily_rest_hours' => env('DRIVER_MAX_DAILY_REST_HOURS', 3),

    /*
    |--------------------------------------------------------------------------
    | Maximum Total Daily Work Hours
    |--------------------------------------------------------------------------
    |
    | The maximum total work hours per day (driving + rest).
    | Standard: 12 hours per day (9 driving + 3 rest).
    | This is used to check compliance violations.
    |
    */

    'max_daily_total_hours' => env('DRIVER_MAX_DAILY_TOTAL_HOURS', 12),

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
            'low' => 1,      // 1 hour over maximum
            'medium' => 2,   // 2 hours over maximum
            'high' => 3,     // 3+ hours over maximum
        ],
        'total_hours' => [
            'low' => 1,      // 1 hour over 12-hour limit
            'medium' => 2,   // 2 hours over 12-hour limit
            'high' => 3,     // 3+ hours over 12-hour limit
        ],
    ],

];

