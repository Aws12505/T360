<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Tenant;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Loop through all tenants
$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    Schedule::command('report:daily', [$tenant->id])
    ->dailyAt('6:00')
    ->timezone($tenant->timezone ?? 'America/Indiana/Indianapolis');

    // Schedule::command('coaching:send', [$tenant->id])
    //     ->weeklyOn(6, '6:00')
    //     ->timezone($tenant->timezone ?? 'America/Indiana/Indianapolis');
}
