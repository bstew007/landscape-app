<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule QBO CDC poll hourly to keep contacts in sync when webhooks are missed
Schedule::command('qbo:cdc:customers')->hourly();
