<?php

namespace App\Console\Commands;

use App\Models\ErpAlertLog;
use App\Models\InventoryItem;
use Illuminate\Console\Command;

class SendLowInventoryAlerts extends Command
{
    protected $signature = 'erp:send-low-inventory-alerts';

    protected $description = 'Log low stock alerts for branch-wise inventory items.';

    public function handle(): int
    {
        $count = 0;

        InventoryItem::where('status', 'active')
            ->whereColumn('current_stock', '<=', 'low_stock_level')
            ->chunkById(100, function ($items) use (&$count) {
                foreach ($items as $item) {
                    ErpAlertLog::firstOrCreate(
                        [
                            'module_name' => 'inventory',
                            'alert_type' => 'low_stock',
                            'title' => 'Low stock: ' . $item->name,
                        ],
                        [
                            'branch_id' => $item->branch_id,
                            'message' => 'Current stock ' . $item->current_stock . ' is at or below low stock level ' . $item->low_stock_level . '.',
                            'status' => 'sent',
                            'payload' => ['inventory_item_id' => $item->id],
                            'sent_at' => now(),
                        ]
                    );
                    $count++;
                }
            });

        $this->info("Low inventory alerts checked: {$count}");

        return self::SUCCESS;
    }
}
