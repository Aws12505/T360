<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReportEmail;
use App\Models\User;

class SendReport extends Command
{
    protected $signature = 'report:daily {tenantId}';
    protected $description = 'Send daily performance report to users with receive_updates permission';

    public function handle()
{
    $tenantId = $this->argument('tenantId');

    $this->info("Running daily report for tenant ID: {$tenantId}");

    // 1) Retrieve users for this tenant who have the 'receive_updates' permission
    $recipients = User::where('tenant_id', $tenantId)
        ->permission('receive_updates')
        ->get();

    if ($recipients->isEmpty()) {
        $this->info('No users with receive_updates permission found for this tenant.');
        return 0;
    }

    // 2) Loop and send
    foreach ($recipients as $user) {
        Mail::to($user->email)
            ->send(new DailyReportEmail($tenantId, $user->name));

        $this->info("Sent report to {$user->email}");
    }

    return 0;
}
}
