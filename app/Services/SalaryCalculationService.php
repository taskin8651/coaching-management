<?php

namespace App\Services;

use App\Models\ExtraClass;
use App\Models\FacultyLogBook;
use App\Models\SalaryPayment;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalaryCalculationService
{
    public function __construct(private WorkingDaysCalculator $workingDaysCalculator)
    {
    }

    public function calculateTeacher(Teacher $teacher, string $salaryMonth): array
    {
        return $teacher->salary_type === 'hourly'
            ? $this->calculateHourlyTeacher($teacher, $salaryMonth)
            : $this->calculateMonthlyTeacher($teacher, $salaryMonth);
    }

    private function calculateHourlyTeacher(Teacher $teacher, string $salaryMonth): array
    {
        $start = Carbon::parse($salaryMonth . '-01')->startOfMonth();
        $end = (clone $start)->endOfMonth();
        $workingDays = $this->workingDaysCalculator->workingDays($teacher->branch_id, $start, $end);
        $minuteRate = (float) ($teacher->minute_rate ?: (($teacher->salary ?: 0) / max(1, $workingDays * 60)));

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
            'salary_type' => 'hourly',
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
                'salary_type' => 'hourly',
                'minute_rate' => $minuteRate,
                'working_days_in_month' => $workingDays,
                'regular_log_ids' => $logs->pluck('id')->all(),
                'extra_class_ids' => $approvedExtraClasses->pluck('id')->all(),
            ],
        ];
    }

    private function calculateMonthlyTeacher(Teacher $teacher, string $salaryMonth): array
    {
        $start = Carbon::parse($salaryMonth . '-01')->startOfMonth();
        $end = (clone $start)->endOfMonth();

        // Monthly pay is flat, but lecture-count stats are still useful context on the payslip.
        $logs = FacultyLogBook::where('teacher_id', $teacher->id)
            ->whereBetween('lecture_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $approvedExtraClasses = ExtraClass::where('teacher_id', $teacher->id)
            ->where('approval_status', 'approved')
            ->whereBetween('class_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $grossSalary = (float) ($teacher->salary ?: 0);
        $extraAmount = (float) $approvedExtraClasses->sum('salary_amount');
        $attendance = $this->absentDeduction($grossSalary, 'teacher_id', $teacher->id, $start, $end, $teacher->branch_id);
        $netSalary = max($grossSalary + $extraAmount - $attendance['deduction_amount'], 0);

        return [
            'employee_type' => 'teacher',
            'salary_type' => 'monthly',
            'teacher_id' => $teacher->id,
            'user_id' => $teacher->user_id,
            'branch_id' => $teacher->branch_id,
            'salary_month' => $salaryMonth,
            'basic_salary' => round($grossSalary, 2),
            'bonus' => round($extraAmount, 2),
            'deduction' => $attendance['deduction_amount'],
            'net_salary' => round($netSalary, 2),
            'paid_amount' => 0,
            'due_amount' => round($netSalary, 2),
            'payment_status' => 'due',
            'total_scheduled_lectures' => $logs->count(),
            'attended_lectures' => $logs->where('is_salary_eligible', true)->count(),
            'missed_lectures' => $logs->whereIn('log_status', ['missed', 'late_entry'])->where('is_salary_eligible', false)->count(),
            'late_joined_lectures' => $logs->filter(fn ($log) => $log->actual_start_time && $log->scheduled_start_time && $log->actual_start_time > $log->scheduled_start_time)->count(),
            'total_scheduled_minutes' => (int) $logs->sum('scheduled_minutes'),
            'total_payable_regular_minutes' => (int) $logs->where('is_salary_eligible', true)->sum('salary_minutes'),
            'approved_extra_class_minutes' => (int) $approvedExtraClasses->sum('salary_minutes'),
            'deducted_minutes' => 0,
            'gross_salary' => round($grossSalary, 2),
            'extra_class_amount' => round($extraAmount, 2),
            'salary_calculation_payload' => [
                'salary_type' => 'monthly',
                'flat_salary' => $grossSalary,
                'extra_class_ids' => $approvedExtraClasses->pluck('id')->all(),
                'absent_days' => $attendance['absent_days'],
                'half_days' => $attendance['half_days'],
                'per_day_rate' => $attendance['per_day_rate'],
                'working_days_in_month' => $attendance['working_days_in_month'],
                'attendance_deduction' => $attendance['deduction_amount'],
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

            $this->preservePaymentRecord($payment, $data);

            $payment->fill($data);
            $payment->save();

            return $payment;
        });
    }

    public function calculateStaff(Staff $staff, string $salaryMonth): array
    {
        return $staff->salary_type === 'hourly'
            ? $this->calculateHourlyStaff($staff, $salaryMonth)
            : $this->calculateMonthlyStaff($staff, $salaryMonth);
    }

    private function calculateHourlyStaff(Staff $staff, string $salaryMonth): array
    {
        $start = Carbon::parse($salaryMonth . '-01')->startOfMonth();
        $end = (clone $start)->endOfMonth();
        $hourlyRate = (float) ($staff->hourly_rate ?: 0);

        $attendances = StaffAttendance::where('staff_id', $staff->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $totalMinutes = (int) $attendances->sum('worked_minutes');
        $grossSalary = ($totalMinutes / 60) * $hourlyRate;

        return [
            'employee_type' => 'staff',
            'salary_type' => 'hourly',
            'staff_id' => $staff->id,
            'user_id' => $staff->user_id,
            'branch_id' => $staff->branch_id,
            'salary_month' => $salaryMonth,
            'basic_salary' => round($grossSalary, 2),
            'bonus' => 0,
            'deduction' => 0,
            'net_salary' => round($grossSalary, 2),
            'paid_amount' => 0,
            'due_amount' => round($grossSalary, 2),
            'payment_status' => 'due',
            'total_scheduled_lectures' => 0,
            'attended_lectures' => $attendances->where('worked_minutes', '>', 0)->count(),
            'missed_lectures' => 0,
            'late_joined_lectures' => 0,
            'total_scheduled_minutes' => 0,
            'total_payable_regular_minutes' => $totalMinutes,
            'approved_extra_class_minutes' => 0,
            'deducted_minutes' => 0,
            'gross_salary' => round($grossSalary, 2),
            'extra_class_amount' => 0,
            'salary_calculation_payload' => [
                'salary_type' => 'hourly',
                'hourly_rate' => $hourlyRate,
                'total_worked_minutes' => $totalMinutes,
                'attendance_ids' => $attendances->pluck('id')->all(),
            ],
        ];
    }

    private function calculateMonthlyStaff(Staff $staff, string $salaryMonth): array
    {
        $start = Carbon::parse($salaryMonth . '-01')->startOfMonth();
        $end = (clone $start)->endOfMonth();

        $attendances = StaffAttendance::where('staff_id', $staff->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $grossSalary = (float) ($staff->salary ?: 0);
        $attendanceDeduction = $this->absentDeduction($grossSalary, 'staff_id', $staff->id, $start, $end, $staff->branch_id);
        $netSalary = max($grossSalary - $attendanceDeduction['deduction_amount'], 0);

        return [
            'employee_type' => 'staff',
            'salary_type' => 'monthly',
            'staff_id' => $staff->id,
            'user_id' => $staff->user_id,
            'branch_id' => $staff->branch_id,
            'salary_month' => $salaryMonth,
            'basic_salary' => round($grossSalary, 2),
            'bonus' => 0,
            'deduction' => $attendanceDeduction['deduction_amount'],
            'net_salary' => round($netSalary, 2),
            'paid_amount' => 0,
            'due_amount' => round($netSalary, 2),
            'payment_status' => 'due',
            'total_scheduled_lectures' => 0,
            'attended_lectures' => $attendances->where('worked_minutes', '>', 0)->count(),
            'missed_lectures' => 0,
            'late_joined_lectures' => 0,
            'total_scheduled_minutes' => 0,
            'total_payable_regular_minutes' => (int) $attendances->sum('worked_minutes'),
            'approved_extra_class_minutes' => 0,
            'deducted_minutes' => 0,
            'gross_salary' => round($grossSalary, 2),
            'extra_class_amount' => 0,
            'salary_calculation_payload' => [
                'salary_type' => 'monthly',
                'flat_salary' => $grossSalary,
                'absent_days' => $attendanceDeduction['absent_days'],
                'half_days' => $attendanceDeduction['half_days'],
                'per_day_rate' => $attendanceDeduction['per_day_rate'],
                'working_days_in_month' => $attendanceDeduction['working_days_in_month'],
                'attendance_deduction' => $attendanceDeduction['deduction_amount'],
            ],
        ];
    }

    public function calculateAndStoreStaff(Staff $staff, string $salaryMonth): SalaryPayment
    {
        return DB::transaction(function () use ($staff, $salaryMonth) {
            $data = $this->calculateStaff($staff, $salaryMonth);
            $payment = SalaryPayment::firstOrNew([
                'staff_id' => $staff->id,
                'salary_month' => $salaryMonth,
                'employee_type' => 'staff',
            ]);

            if (! $payment->slip_no) {
                $payment->slip_no = $this->generateSlipNo();
            }

            $this->preservePaymentRecord($payment, $data);

            $payment->fill($data);
            $payment->save();

            return $payment;
        });
    }

    /**
     * calculateTeacher()/calculateStaff() always return paid_amount=0/payment_status='due' since
     * they only compute what is OWED, not what has been PAID. Re-running "Calculate Salary" on a
     * payment that was already recorded as paid/partial must not blow that away — so once a
     * SalaryPayment row already exists, its actual payment-tracking fields are preserved here and
     * due_amount/payment_status are re-derived against the freshly calculated net_salary instead
     * of being overwritten with the calculator's due-only defaults.
     */
    private function preservePaymentRecord(SalaryPayment $payment, array &$data): void
    {
        if (! $payment->exists) {
            return;
        }

        $paidAmount = (float) $payment->paid_amount;

        $data['paid_amount'] = $paidAmount;
        $data['payment_mode'] = $payment->payment_mode;
        $data['payment_date'] = $payment->payment_date;
        $data['paid_by_id'] = $payment->paid_by_id;
        $data['remarks'] = $payment->remarks;

        if ($payment->payment_status === 'cancelled') {
            $data['payment_status'] = 'cancelled';
            $data['due_amount'] = 0;

            return;
        }

        $netSalary = (float) $data['net_salary'];
        $data['due_amount'] = round(max($netSalary - $paidAmount, 0), 2);
        $data['payment_status'] = $paidAmount >= $netSalary && $netSalary > 0
            ? 'paid'
            : ($paidAmount > 0 ? 'partial' : 'due');
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

    /**
     * Monthly (flat-salary) employees still lose pay for days they were marked absent — a
     * half_day counts as half an absence. Per-day rate uses the branch's actual working-day
     * count for the month (calendar days minus that branch's weekly-off day minus mandatory
     * holidays — see WorkingDaysCalculator), not a fixed 26-day assumption.
     */
    private function absentDeduction(float $monthlySalary, string $employeeColumn, int $employeeId, Carbon $start, Carbon $end, ?int $branchId): array
    {
        $absentDays = StaffAttendance::where($employeeColumn, $employeeId)
            ->where('status', 'absent')
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->count();

        $halfDays = StaffAttendance::where($employeeColumn, $employeeId)
            ->where('status', 'half_day')
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->count();

        $workingDays = $this->workingDaysCalculator->workingDays($branchId, $start, $end);
        $perDayRate = $monthlySalary / $workingDays;
        $deductibleDays = $absentDays + ($halfDays * 0.5);

        return [
            'absent_days' => $absentDays,
            'half_days' => $halfDays,
            'working_days_in_month' => $workingDays,
            'per_day_rate' => round($perDayRate, 2),
            'deduction_amount' => round($deductibleDays * $perDayRate, 2),
        ];
    }

    private function generateSlipNo(): string
    {
        $lastPayment = SalaryPayment::latest('id')->first();
        $nextId = $lastPayment ? $lastPayment->id + 1 : 1;

        return 'SAL-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }
}
