@extends('layouts.admin')

@section('page-title', 'Salary Reports')

@section('content')
<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Salary Reports</h2>
        <p class="admin-page-subtitle">Regular payable minutes and approved extra class minutes</p>
    </div>
</div>

@can('salary_calculate')
<div class="page-card" style="margin-bottom:16px;">
    <div class="page-card-header">
        <p class="page-card-title">Calculate Salary</p>

        <span class="page-card-note">
            <i class="fas fa-calculator"></i>
            Monthly = flat salary · Hourly = attendance/lecture minutes × rate
        </span>
    </div>

    <form method="POST" action="{{ route('admin.salary-reports.calculate') }}" style="padding:16px;">
        @csrf

        <div class="admin-form-grid" style="grid-template-columns: repeat(3, 1fr); gap:12px; margin:0;">
            <div class="field-group mb-0">
                <label class="field-label" for="calc_employee_type">Employee Type</label>
                <select name="employee_type" id="calc_employee_type" class="field-input" required>
                    <option value="teacher">Teacher</option>
                    <option value="staff">Staff</option>
                </select>
            </div>

            <div class="field-group mb-0" id="calcTeacherBox">
                <label class="field-label">Teacher</label>
                <select name="teacher_id" class="field-input">
                    @foreach($teachers as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field-group mb-0" id="calcStaffBox" style="display:none;">
                <label class="field-label">Staff</label>
                <select name="staff_id" class="field-input">
                    @foreach($staffList as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field-group mb-0">
                <label class="field-label">Month</label>
                <input type="month" name="salary_month" value="{{ $salaryMonth }}" class="field-input" required>
            </div>
        </div>

        <div class="action-row" style="justify-content:flex-start; margin-top:14px;">
            <button class="btn-primary" type="submit">
                <i class="fas fa-calculator"></i>
                Calculate Salary
            </button>
        </div>
    </form>
</div>
@endcan

<div class="page-card" style="margin-bottom:16px;">
    <div class="page-card-header">
        <p class="page-card-title">Filters</p>
    </div>

    <form method="GET" action="{{ route('admin.salary-reports.index') }}" style="padding:16px;">
        <div class="admin-form-grid" style="grid-template-columns: repeat(4, 1fr); gap:12px;">

            <div class="field-group mb-0">
                <label class="field-label" for="filter_report_month">Month</label>
                <input type="month" name="salary_month" id="filter_report_month" value="{{ $filters['salary_month'] ?? $salaryMonth }}" class="field-input">
            </div>

            <div class="field-group mb-0">
                <label class="field-label" for="filter_report_employee_type">Employee Type</label>
                <select name="employee_type" id="filter_report_employee_type" class="field-input">
                    <option value="">All Types</option>
                    <option value="teacher" {{ ($filters['employee_type'] ?? '') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                    <option value="staff" {{ ($filters['employee_type'] ?? '') == 'staff' ? 'selected' : '' }}>Staff</option>
                </select>
            </div>

            <div class="field-group mb-0">
                <label class="field-label" for="filter_report_salary_type">Pay Basis</label>
                <select name="salary_type" id="filter_report_salary_type" class="field-input">
                    <option value="">All</option>
                    <option value="monthly" {{ ($filters['salary_type'] ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="hourly" {{ ($filters['salary_type'] ?? '') == 'hourly' ? 'selected' : '' }}>Hourly</option>
                </select>
            </div>

            <div class="field-group mb-0">
                <label class="field-label" for="filter_report_employee_id">Employee</label>
                <input type="number"
                       name="employee_id"
                       id="filter_report_employee_id"
                       value="{{ $filters['employee_id'] ?? '' }}"
                       placeholder="Teacher/Staff ID (optional)"
                       class="field-input">
            </div>

        </div>

        <div class="action-row" style="justify-content:flex-start; gap:10px; margin-top:14px;">
            <button type="submit" class="btn-primary">
                <i class="fas fa-filter"></i>
                Apply Filters
            </button>

            <a href="{{ route('admin.salary-reports.index') }}" class="btn-ghost">
                Clear
            </a>
        </div>
    </form>
</div>

<div class="page-card">
    <div class="page-card-table">
        <table class="min-w-full datatable datatable-SalaryReports">
            <thead>
                <tr>
                    <th>Employee</th><th>Pay Basis</th><th>Month</th><th>Scheduled</th><th>Attended</th><th>Missed</th><th>Late</th><th>Scheduled Min</th><th>Regular Min</th><th>Extra Min</th><th>Deducted Min</th><th>Gross</th><th>Extra Amount</th><th>Net</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td>{{ $payment->employee_name }}</td>
                        <td>
                            @if($payment->salary_type == 'hourly')
                                <span class="code-pill">Hourly</span>
                            @elseif($payment->salary_type == 'monthly')
                                <span class="code-pill">Monthly</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $payment->salary_month }}</td>
                        <td>{{ $payment->total_scheduled_lectures }}</td>
                        <td>{{ $payment->attended_lectures }}</td>
                        <td>{{ $payment->missed_lectures }}</td>
                        <td>{{ $payment->late_joined_lectures }}</td>
                        <td>{{ $payment->total_scheduled_minutes }}</td>
                        <td>{{ $payment->total_payable_regular_minutes }}</td>
                        <td>{{ $payment->approved_extra_class_minutes }}</td>
                        <td>{{ $payment->deducted_minutes }}</td>
                        <td>{{ number_format($payment->gross_salary, 2) }}</td>
                        <td>{{ number_format($payment->extra_class_amount, 2) }}</td>
                        <td>{{ number_format($payment->net_salary, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
$(function(){initAdminDataTable('.datatable-SalaryReports',{searchPlaceholder:'Search salary reports...'});});

document.addEventListener('DOMContentLoaded', function () {
    const calcEmployeeType = document.getElementById('calc_employee_type');
    const calcTeacherBox = document.getElementById('calcTeacherBox');
    const calcStaffBox = document.getElementById('calcStaffBox');

    if (!calcEmployeeType) return;

    function toggleCalcBox() {
        const isTeacher = calcEmployeeType.value === 'teacher';
        calcTeacherBox.style.display = isTeacher ? 'block' : 'none';
        calcStaffBox.style.display = isTeacher ? 'none' : 'block';
    }

    calcEmployeeType.addEventListener('change', toggleCalcBox);
    toggleCalcBox();
});
</script>
@endsection
