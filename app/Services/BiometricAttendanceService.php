<?php

namespace App\Services;

use App\Models\AttendanceSetting;
use App\Models\Batch;
use App\Models\BiometricDeviceLog;
use App\Models\FacultyLogBook;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Teacher;
use App\Models\Timetable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BiometricAttendanceService
{
    public function normalizePunchType(?string $punchType): string
    {
        $normalized = strtolower(trim((string) ($punchType ?? '')));

        return in_array($normalized, ['in', 'out'], true) ? $normalized : 'in';
    }

    public function process(BiometricDeviceLog $log): void
    {
        DB::transaction(function () use ($log) {
            match ($log->user_type) {
                'student' => $this->processStudentLog($log),
                'teacher' => $this->processTeacherLog($log),
                'staff' => $this->processStaffLog($log),
            };
        });
    }

    private function processStudentLog(BiometricDeviceLog $log): void
    {
        $student = Student::where('biometric_id', $log->biometric_user_id)->first();

        if (! $student) {
            $this->fail($log, 'Student biometric id not found.');
            return;
        }

        $batchAssignment = $this->matchStudentBatch($student, $log);

        if (! $batchAssignment) {
            $this->fail($log, 'No active batch matched the punch time.');
            return;
        }

        $batch = $batchAssignment->batch;
        $date = $log->punch_time->toDateString();
        $time = $log->punch_time->format('H:i:s');
        $punchType = $this->normalizePunchType($log->punch_type);

        $attendance = StudentAttendance::firstOrNew([
            'student_id' => $student->id,
            'batch_id' => $batch->id,
            'subject_id' => $batchAssignment->subject_id,
            'attendance_date' => $date,
        ]);

        $attendance->fill([
            'scheduled_start_time' => $batch->start_time,
            'scheduled_end_time' => $batch->end_time,
            'source' => 'biometric',
            'biometric_device_log_id' => $log->id,
        ]);

        $shouldSendCheckIn = $punchType === 'in' && ! $attendance->actual_in_time;

        if ($punchType === 'in') {
            $attendance->actual_in_time = $attendance->actual_in_time
                ? min($attendance->actual_in_time, $time)
                : $time;
        } else {
            $attendance->actual_out_time = $attendance->actual_out_time
                ? max($attendance->actual_out_time, $time)
                : $time;
        }

        $attendance->status = $this->studentStatus($batch, $attendance->actual_in_time);
        $attendance->save();

        if ($shouldSendCheckIn) {
            app(WhatsappService::class)->sendStudentBiometricCheckIn($student, $log->punch_time);
        }

        $this->processed($log, 'Student attendance processed.');
    }

    private function processTeacherLog(BiometricDeviceLog $log): void
    {
        $teacher = Teacher::where('biometric_id', $log->biometric_user_id)->first();

        if (! $teacher) {
            $this->fail($log, 'Teacher biometric id not found.');
            return;
        }

        $this->upsertStaffAttendance($log, $teacher->user_id, $teacher->branch_id, $teacher->id, null);
        $this->syncTeacherFacultyLog($teacher, $log);
        $this->processed($log, 'Teacher attendance processed.');
    }

    private function processStaffLog(BiometricDeviceLog $log): void
    {
        $staff = Staff::where('biometric_id', $log->biometric_user_id)->first();

        if (! $staff) {
            $this->fail($log, 'Staff biometric id not found.');
            return;
        }

        $this->upsertStaffAttendance($log, $staff->user_id, $staff->branch_id, null, $staff->id);
        $this->processed($log, 'Staff attendance processed.');
    }

    private function matchStudentBatch(Student $student, BiometricDeviceLog $log)
    {
        $setting = AttendanceSetting::current();
        $date = $log->punch_time->toDateString();
        $time = $log->punch_time;

        return $student->studentBatches()
            ->with(['batch', 'subject'])
            ->where('status', 'active')
            ->where(function ($query) use ($date) {
                $query->whereNull('start_date')->orWhereDate('start_date', '<=', $date);
            })
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')->orWhereDate('end_date', '>=', $date);
            })
            ->get()
            ->filter(function ($assignment) use ($time, $setting) {
                if (! $assignment->batch || ! $assignment->batch->start_time || ! $assignment->batch->end_time) {
                    return false;
                }

                $start = Carbon::parse($time->toDateString() . ' ' . $assignment->batch->start_time)
                    ->subMinutes($setting->student_grace_minutes);
                $end = Carbon::parse($time->toDateString() . ' ' . $assignment->batch->end_time)
                    ->addMinutes($setting->student_grace_minutes);

                return $time->betweenIncluded($start, $end);
            })
            ->sortBy(function ($assignment) use ($time) {
                $start = Carbon::parse($time->toDateString() . ' ' . $assignment->batch->start_time);
                return abs($time->diffInSeconds($start, false));
            })
            ->first();
    }

    private function studentStatus(Batch $batch, ?string $actualIn): string
    {
        if (! $actualIn) {
            return 'absent';
        }

        $setting = AttendanceSetting::current();
        $actual = Carbon::parse($actualIn);
        $start = Carbon::parse($batch->start_time)->addMinutes($setting->student_grace_minutes);

        return $actual->greaterThan($start) ? 'late' : 'present';
    }

    private function upsertStaffAttendance(BiometricDeviceLog $log, $userId, $branchId, $teacherId, $staffId): void
    {
        $attendance = StaffAttendance::firstOrNew([
            'user_id' => $userId,
            'attendance_date' => $log->punch_time->toDateString(),
        ]);

        $time = $log->punch_time->format('H:i:s');
        $punchType = $this->normalizePunchType($log->punch_type);

        $attendance->fill([
            'teacher_id' => $teacherId,
            'staff_id' => $staffId,
            'branch_id' => $branchId,
            'source' => 'biometric',
            'status' => 'present',
        ]);

        if ($punchType === 'in') {
            $attendance->first_in_time = $attendance->first_in_time
                ? min($attendance->first_in_time, $time)
                : $time;
        } else {
            $attendance->last_out_time = $attendance->last_out_time
                ? max($attendance->last_out_time, $time)
                : $time;
        }

        if ($attendance->first_in_time && $attendance->last_out_time) {
            $attendance->worked_minutes = Carbon::parse($attendance->first_in_time)
                ->diffInMinutes(Carbon::parse($attendance->last_out_time));
        }

        $attendance->save();
    }

    private function syncTeacherFacultyLog(Teacher $teacher, BiometricDeviceLog $log): void
    {
        $setting = AttendanceSetting::current();
        $date = $log->punch_time->toDateString();
        $time = $log->punch_time;

        $timetable = Timetable::where('teacher_id', $teacher->id)
            ->where('status', 'scheduled')
            ->where(function ($query) use ($date, $time) {
                $query->whereDate('schedule_date', $date)
                    ->orWhere('day_of_week', $time->format('l'));
            })
            ->get()
            ->filter(function ($row) use ($time, $setting) {
                if (! $row->start_time || ! $row->end_time) {
                    return false;
                }

                $start = Carbon::parse($time->toDateString() . ' ' . $row->start_time)
                    ->subMinutes($setting->teacher_grace_minutes);
                $end = Carbon::parse($time->toDateString() . ' ' . $row->end_time)
                    ->addMinutes($setting->teacher_grace_minutes);

                return $time->betweenIncluded($start, $end);
            })
            ->sortBy(function ($row) use ($time) {
                $start = Carbon::parse($time->toDateString() . ' ' . $row->start_time);
                return abs($time->diffInSeconds($start, false));
            })
            ->first();

        if (! $timetable) {
            return;
        }

        $logBook = FacultyLogBook::firstOrNew([
            'teacher_id' => $teacher->id,
            'batch_id' => $timetable->batch_id,
            'subject_id' => $timetable->subject_id,
            'lecture_date' => $date,
            'scheduled_start_time' => $timetable->start_time,
            'scheduled_end_time' => $timetable->end_time,
        ]);

        $punchTime = $log->punch_time->format('H:i:s');
        $punchType = $this->normalizePunchType($log->punch_type);

        if ($punchType === 'in') {
            $logBook->actual_start_time = $logBook->actual_start_time
                ? min($logBook->actual_start_time, $punchTime)
                : $punchTime;
        } else {
            $logBook->actual_end_time = $logBook->actual_end_time
                ? max($logBook->actual_end_time, $punchTime)
                : $punchTime;
        }

        $minutes = app(SalaryCalculationService::class)->payableMinutes(
            $timetable->start_time,
            $timetable->end_time,
            $logBook->actual_start_time,
            $logBook->actual_end_time
        );

        $late = $logBook->actual_start_time && Carbon::parse($logBook->actual_start_time)->greaterThan(Carbon::parse($timetable->start_time)->addMinutes($setting->teacher_grace_minutes));

        $logBook->fill([
            'branch_id' => $teacher->branch_id,
            'log_status' => $late ? 'late_entry' : 'draft',
            'approval_status' => 'pending',
            'scheduled_minutes' => $minutes['scheduled_minutes'],
            'salary_minutes' => $minutes['salary_minutes'],
            'is_salary_eligible' => false,
        ]);

        $logBook->save();
    }

    private function processed(BiometricDeviceLog $log, string $message): void
    {
        $log->update([
            'processed_status' => 'processed',
            'processing_message' => $message,
            'processed_at' => now(),
        ]);
    }

    private function fail(BiometricDeviceLog $log, string $message): void
    {
        $log->update([
            'processed_status' => 'failed',
            'processing_message' => $message,
            'processed_at' => now(),
        ]);
    }

}
