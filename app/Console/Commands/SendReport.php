<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReportEmail;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendReport extends Command
{
    protected $signature = 'report:daily {tenantId}';
    protected $description = 'Send daily performance report to users with receive_updates permission';

    public function handle()
{
    $tenantId = $this->argument('tenantId');

    $message = "Running daily report for tenant ID: {$tenantId}";
    $this->info($message);
    Log::channel('daily')->info($message);

    // Retrieve users
    $recipients = User::where('tenant_id', $tenantId)
        ->permission('receive_updates')
        ->get();

    if ($recipients->isEmpty()) {
        $message = 'No users with receive_updates permission found for this tenant.';
        $this->info($message);
        Log::channel('daily')->info($message);
        return 0;
    }

    foreach ($recipients as $user) {
        Mail::to($user->email)
            ->send(new DailyReportEmail($tenantId, $user->name));

        $message = "Sent report to {$user->email}";
        $this->info($message);
        Log::channel('daily')->info($message);
    }

    return 0;
}
}
