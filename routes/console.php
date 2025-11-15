<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Auto-update coaching sessions status daily at midnight
Schedule::command('coaching-sessions:update-status')
    ->daily()
    ->at('00:00')
    ->timezone('Africa/Casablanca') // Adjust timezone as needed
    ->description('Automatically update coaching sessions from planned to in_progress when date is today or in the past');
