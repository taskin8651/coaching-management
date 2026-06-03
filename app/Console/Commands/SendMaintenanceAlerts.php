<?php

namespace App\Console\Commands;

use App\Models\ErpAlertLog;
use App\Models\MaintenanceRequest;
use Illuminate\Console\Command;

class SendMaintenanceAlerts extends Command
{
    protected $signature = 'erp:send-maintenance-alerts';

    protected $description = 'Log pending maintenance alerts for managers/admin review.';

    public function handle(): int
    {
        $count = 0;

        MaintenanceRequest::whereIn('status', ['open', 'assigned', 'in_progress'])
            ->whereIn('priority', ['high', 'urgent'])
            ->chunkById(100, function ($requests) use (&$count) {
                foreach ($requests as $request) {
                    ErpAlertLog::firstOrCreate(
                        [
                            'module_name' => 'maintenance',
                            'alert_type' => 'pending_high_priority',
                            'title' => 'Pending maintenance: ' . $request->title,
                        ],
                        [
                            'branch_id' => $request->branch_id,
                            'message' => $request->description,
                            'status' => 'sent',
                            'payload' => ['maintenance_request_id' => $request->id],
                            'sent_at' => now(),
                        ]
                    );
                    $count++;
                }
            });

        $this->info("Maintenance alerts checked: {$count}");

        return self::SUCCESS;
    }
}
