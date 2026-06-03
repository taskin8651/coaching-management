<?php

namespace App\Console\Commands;

use App\Models\FacultyLogBook;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkMissedFacultyLogs extends Command
{
    protected $signature = 'erp:mark-missed-faculty-logs';
    protected $description = 'Mark unsubmitted faculty logs older than 24 hours as late entry and salary ineligible.';

    public function handle(): int
    {
        $count = FacultyLogBook::whereIn('log_status', ['draft'])
            ->where('lecture_date', '<=', Carbon::yesterday()->toDateString())
            ->update([
                'log_status' => 'late_entry',
                'approval_status' => 'pending',
                'is_salary_eligible' => false,
                'salary_minutes' => 0,
            ]);

        $this->info("Marked {$count} faculty logs as late entry.");

        return self::SUCCESS;
    }
}
