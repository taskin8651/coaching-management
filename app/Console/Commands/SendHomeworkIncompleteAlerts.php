<?php

namespace App\Console\Commands;

use App\Models\HomeworkSubmission;
use App\Services\WhatsappService;
use Illuminate\Console\Command;

class SendHomeworkIncompleteAlerts extends Command
{
    protected $signature = 'erp:send-homework-incomplete-alerts';
    protected $description = 'Send WhatsApp alerts for incomplete homework after due date.';

    public function handle(WhatsappService $whatsapp): int
    {
        $submissions = HomeworkSubmission::with(['homework', 'student'])
            ->whereIn('status', ['pending', 'incomplete'])
            ->whereHas('homework', fn ($query) => $query->whereDate('due_date', '<', now()->toDateString()))
            ->get();

        foreach ($submissions as $submission) {
            $whatsapp->sendStudentGuardianMessage($submission->student, 'homework', 'Homework incomplete: '.$submission->homework->title.'.');
        }

        $this->info("Processed {$submissions->count()} homework alerts.");

        return self::SUCCESS;
    }
}
