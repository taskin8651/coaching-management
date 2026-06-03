<?php

namespace App\Services;

use App\Models\ExtraClass;
use App\Models\FacultyLogBook;
use App\Models\SalaryPayment;
use App\Models\StaffAttendance;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalaryCalculationService
{
    public function calculateTeacher(Teacher $teacher, string $salaryMonth): array
    {
        $start = Carbon::parse($salaryMonth . '-01')->startOfMonth();
        $end = (clone $start)->endOfMonth();
        $minuteRate = (float) ($teacher->minute_rate ?: (($teacher->salary ?: 0) / max(1, 26 * 60)));

        $logs = FacultyLogBook::where('teacher_id', $teacher->id)
            ->whereBetween('lecture_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $approvedExtraClasses = ExtraClass::where('teacher_id', $teacher->id)
            ->where('approval_status', 'approved')
            ->whereBetween('class_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $scheduledMinutes = (int) $logs->sum('scheduled_minutes');
        $regularMinutes = (int) $logs->where('is_salary_eligible', true)->sum('salary_minutes');
        $extraMinutes = (int) $approvedExtraClasses->sum('salary_minutes');
        $grossSalary = ($regularMinutes * $minuteRate);
        $extraAmount = $approvedExtraClasses->sum('salary_amount') ?: ($extraMinutes * $minuteRate);
        $deductedMinutes = max($scheduledMinutes - $regularMinutes, 0);

        return [
            'employee_type' => 'teacher',
            'teacher_id' => $teacher->id,
            'user_id' => $teacher->user_id,
            'branch_id' => $teacher->branch_id,
            'salary_month' => $salaryMonth,
            'basic_salary' => round($grossSalary, 2),
            'bonus' => round($extraAmount, 2),
            'deduction' => 0,
            'net_salary' => round($grossSalary + $extraAmount, 2),
            'paid_amount' => 0,
            'due_amount' => round($grossSalary + $extraAmount, 2),
            'payment_status' => 'due',
            'total_scheduled_lectures' => $logs->count(),
            'attended_lectures' => $logs->where('is_salary_eligible', true)->count(),
            'missed_lectures' => $logs->whereIn('log_status', ['missed', 'late_entry'])->where('is_salary_eligible', false)->count(),
            'late_joined_lectures' => $logs->filter(fn ($log) => $log->actual_start_time && $log->scheduled_start_time && $log->actual_start_time > $log->scheduled_start_time)->count(),
            'total_scheduled_minutes' => $scheduledMinutes,
            'total_payable_regular_minutes' => $regularMinutes,
            'approved_extra_class_minutes' => $extraMinutes,
            'deducted_minutes' => $deductedMinutes,
            'gross_salary' => round($grossSalary, 2),
            'extra_class_amount' => round($extraAmount, 2),
            'salary_calculation_payload' => [
                'minute_rate' => $minuteRate,
                'regular_log_ids' => $logs->pluck('id')->all(),
                'extra_class_ids' => $approvedExtraClasses->pluck('id')->all(),
            ],
        ];
    }

    public function calculateAndStoreTeacher(Teacher $teacher, string $salaryMonth): SalaryPayment
    {
        return DB::transaction(function () use ($teacher, $salaryMonth) {
            $data = $this->calculateTeacher($teacher, $salaryMonth);
            $payment = SalaryPayment::firstOrNew([
                'teacher_id' => $teacher->id,
                'salary_month' => $salaryMonth,
                'employee_type' => 'teacher',
            ]);

            if (! $payment->slip_no) {
                $payment->slip_no = $this->generateSlipNo();
            }

            $payment->fill($data);
            $payment->save();

            return $payment;
        });
    }

    public function payableMinutes(?string $scheduledStart, ?string $scheduledEnd, ?string $actualStart, ?string $actualEnd): array
    {
        if (! $scheduledStart || ! $scheduledEnd || ! $actualStart) {
            return ['scheduled_minutes' => 0, 'salary_minutes' => 0];
        }

        $scheduledStartAt = Carbon::parse($scheduledStart);
        $scheduledEndAt = Carbon::parse($scheduledEnd);
        $actualStartAt = Carbon::parse($actualStart);
        $actualEndAt = $actualEnd ? Carbon::parse($actualEnd) : $scheduledEndAt;

        $payableStart = $actualStartAt->greaterThan($scheduledStartAt) ? $actualStartAt : $scheduledStartAt;
        $payableEnd = $actualEndAt->lessThan($scheduledEndAt) ? $actualEndAt : $scheduledEndAt;

        return [
            'scheduled_minutes' => max($scheduledStartAt->diffInMinutes($scheduledEndAt), 0),
            'salary_minutes' => $payableEnd->greaterThan($payableStart) ? $payableStart->diffInMinutes($payableEnd) : 0,
        ];
    }

    private function generateSlipNo(): string
    {
        $lastPayment = SalaryPayment::latest('id')->first();
        $nextId = $lastPayment ? $lastPayment->id + 1 : 1;

        return 'SAL-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }
}
