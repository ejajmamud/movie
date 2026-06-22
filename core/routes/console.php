<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Schedule::call(function () {
    if (Schema::hasTable('general_settings')) {
        $general = DB::table('general_settings')->first();
        if (@$general->auto_sync) {
            Artisan::call('movies:sync', ['--limit' => 10, '--type' => 'all']);
        }
    }
})->daily();

Schedule::call(function () {
    if (Schema::hasTable('general_settings')) {
        $general = DB::table('general_settings')->first();
        if (@$general->auto_repair_posters) {
            Artisan::call('movies:repair-posters');
        }
    }
})->weekly();
