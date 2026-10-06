<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:criar --manter=15')
    ->dailyAt('03:00')
    ->when(fn (): bool => config('database.default') === 'sqlite')
    ->withoutOverlapping(60);
Schedule::command('sistema:verificar')->hourly()->withoutOverlapping(10);
