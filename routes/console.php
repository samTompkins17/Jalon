<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Alertes de surestarie générées chaque matin (aucun doublon grâce à la clé unique).
Schedule::command('jalon:alertes')->dailyAt('07:00');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
