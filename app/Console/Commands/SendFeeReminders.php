<?php

namespace App\Console\Commands;

use App\Models\FeeInstallment;
use App\Services\WhatsappService;
use Illuminate\Console\Command;

class SendFeeReminders extends Command
{
    protected $signature = 'erp:send-fee-reminders';
    protected $description = 'Send WhatsApp reminders for due and overdue fee installments.';

    public function handle(WhatsappService $whatsapp): int
    {
        $installments = FeeInstallment::with('student')
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->whereDate('due_date', '<=', now()->addDays(2)->toDateString())
            ->where(function ($query) {
                $query->whereNull('reminded_at')->orWhereDate('reminded_at', '<', now()->toDateString());
            })
            ->get();

        $sent = 0;

        foreach ($installments as $installment) {
            $log = $whatsapp->sendStudentGuardianMessage($installment->student, 'fee_due', 'Fee due reminder: '.$installment->title.' amount '.$installment->due_amount.' due on '.optional($installment->due_date)->format('d M Y'));

            if ($log->status === 'sent') {
                $installment->update(['reminded_at' => now()]);
                $sent++;
            }
        }

        $this->info("Processed {$installments->count()} fee reminders, {$sent} sent successfully.");

        return self::SUCCESS;
    }
}
