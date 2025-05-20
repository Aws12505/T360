<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyReportEmail;
use App\Models\User;

class SendReport extends Command
{
    protected $signature = 'report:daily';
    protected $description = 'Send daily performance report to users with receive_updates permission';

    public function handle()
    {
        // 1) Retrieve all users who have the 'receive_updates' permission
        $recipients = User::permission('receive_updates')->get();

        if ($recipients->isEmpty()) {
            $this->info('No users with receive_updates permission found.');
            return 0;
        }

        // 2) Loop and send
        foreach ($recipients as $user) {
            // Pass tenant_id explicitly
            $tenantId = $user->tenant_id;
            if(!is_null($tenantId)) {
                Mail::to($user->email)
                    ->send(new DailyReportEmail($tenantId, $user->name));
                $this->info("Sent report to {$user->email}");
            }
        }

        return 0;
    }
}
