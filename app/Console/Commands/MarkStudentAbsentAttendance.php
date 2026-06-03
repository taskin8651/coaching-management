<?php

namespace App\Console\Commands;

use App\Models\AttendanceSetting;
use App\Models\Batch;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Services\WhatsappService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkStudentAbsentAttendance extends Command
{
    protected $signature = 'erp:mark-student-absents {date?}';

    protected $description = 'Create batch-wise absent attendance records after configured cut-off.';

    public function handle(WhatsappService $whatsapp): int
    {
        $date = Carbon::parse($this->argument('date') ?: today())->toDateString();
        $setting = AttendanceSetting::current();
        $created = 0;

        Batch::where('status', 'active')
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->with(['assignedStudents.user'])
            ->chunkById(100, function ($batches) use ($date, $setting, $whatsapp, &$created) {
                foreach ($batches as $batch) {
                    $cutoff = Carbon::parse($date . ' ' . $batch->start_time)->addMinutes($setting->auto_absent_after_minutes);

                    if (now()->lt($cutoff)) {
                        continue;
                    }

                    $students = $batch->assignedStudents->isNotEmpty()
                        ? $batch->assignedStudents
                        : Student::where('batch_id', $batch->id)->with('user')->get();

                    foreach ($students as $student) {
                        $attendance = StudentAttendance::firstOrCreate(
                            [
                                'student_id' => $student->id,
                                'batch_id' => $batch->id,
                                'subject_id' => $student->pivot->subject_id ?? null,
                                'attendance_date' => $date,
                            ],
                            [
                                'scheduled_start_time' => $batch->start_time,
                                'scheduled_end_time' => $batch->end_time,
                                'status' => 'absent',
                                'source' => 'manual',
                                'remarks' => 'Auto-marked absent after grace window.',
                            ]
                        );

                        if ($attendance->wasRecentlyCreated) {
                            $created++;
                            $whatsapp->sendStudentGuardianMessage(
                                $student,
                                'attendance',
                                'Your child ' . ($student->user->name ?? 'Student') . ' is absent for ' . $batch->name . ' Batch ' . $batch->start_time . ' - ' . $batch->end_time . '.'
                            );
                        }
                    }
                }
            });

        $this->info("Student absent records created: {$created}");

        return self::SUCCESS;
    }
}
