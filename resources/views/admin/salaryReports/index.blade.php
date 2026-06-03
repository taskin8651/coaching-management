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
<form method="POST" action="{{ route('admin.salary-reports.calculate') }}" class="page-card" style="padding:18px; margin-bottom:18px;">
    @csrf
    <div class="admin-form-grid" style="margin:0;">
        <div class="field-group">
            <label class="field-label">Teacher</label>
            <select name="teacher_id" class="field-input" required>
                @foreach($teachers as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="field-group">
            <label class="field-label">Month</label>
            <input type="month" name="salary_month" value="{{ $salaryMonth }}" class="field-input" required>
        </div>
        <div class="field-group" style="align-self:end;">
            <button class="btn-primary" type="submit"><i class="fas fa-calculator"></i> Calculate Salary</button>
        </div>
    </div>
</form>
@endcan

<div class="page-card">
    <div class="page-card-table">
        <table class="min-w-full datatable datatable-SalaryReports">
            <thead>
                <tr>
                    <th>Employee</th><th>Month</th><th>Scheduled</th><th>Attended</th><th>Missed</th><th>Late</th><th>Scheduled Min</th><th>Regular Min</th><th>Extra Min</th><th>Deducted Min</th><th>Gross</th><th>Extra Amount</th><th>Net</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td>{{ $payment->employee_name }}</td>
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
<script>$(function(){initAdminDataTable('.datatable-SalaryReports',{searchPlaceholder:'Search salary reports...'});});</script>
@endsection
