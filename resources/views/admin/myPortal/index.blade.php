@extends('layouts.admin')

@section('content')
<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">My Portal</h2>
        <p class="admin-page-subtitle">Personal academic, fee, attendance and salary visibility.</p>
    </div>
</div>

<div class="admin-table-card">
    <div class="card-header"><h3>Student Attendance</h3></div>
    <div class="table-responsive"><table class="table table-bordered table-striped table-hover datatable">
        <thead><tr><th>Student</th><th>Date</th><th>Batch</th><th>Subject</th><th>Status</th><th>Time</th></tr></thead>
        <tbody>@foreach($studentAttendances as $row)<tr><td>{{ $row->student->user->name ?? '-' }}</td><td>{{ $row->attendance_date }}</td><td>{{ $row->batch->name ?? '-' }}</td><td>{{ $row->subject->name ?? '-' }}</td><td>{{ ucfirst($row->status) }}</td><td>{{ $row->actual_in_time ?? '-' }} - {{ $row->actual_out_time ?? '-' }}</td></tr>@endforeach</tbody>
    </table></div>
</div>

<div class="admin-table-card">
    <div class="card-header"><h3>Homework</h3></div>
    <div class="table-responsive"><table class="table table-bordered table-striped table-hover datatable">
        <thead><tr><th>Title</th><th>Batch</th><th>Subject</th><th>Due Date</th><th>Status</th></tr></thead>
        <tbody>@foreach($homeworks as $row)<tr><td>{{ $row->title }}</td><td>{{ $row->batch->name ?? '-' }}</td><td>{{ $row->subject->name ?? '-' }}</td><td>{{ $row->due_date ?? '-' }}</td><td>{{ ucfirst($row->status) }}</td></tr>@endforeach</tbody>
    </table></div>
</div>

<div class="admin-table-card">
    <div class="card-header"><h3>Fees & Results</h3></div>
    <div class="table-responsive"><table class="table table-bordered table-striped table-hover">
        <thead><tr><th>Fee Installment</th><th>Amount</th><th>Paid</th><th>Due</th><th>Due Date</th><th>Status</th></tr></thead>
        <tbody>@foreach($feeInstallments as $row)<tr><td>{{ $row->title }}</td><td>{{ $row->amount }}</td><td>{{ $row->paid_amount }}</td><td>{{ $row->due_amount }}</td><td>{{ $row->due_date ?? '-' }}</td><td>{{ ucfirst($row->status) }}</td></tr>@endforeach</tbody>
    </table></div>
    <div class="table-responsive mt-3"><table class="table table-bordered table-striped table-hover">
        <thead><tr><th>Exam</th><th>Marks</th><th>Percentage</th><th>Grade</th><th>Rank</th></tr></thead>
        <tbody>@foreach($reportCards as $row)<tr><td>{{ $row->exam->title ?? '-' }}</td><td>{{ $row->marks_obtained }}/{{ $row->total_marks }}</td><td>{{ $row->percentage }}%</td><td>{{ $row->grade }}</td><td>{{ $row->rank ?? '-' }}</td></tr>@endforeach</tbody>
    </table></div>
</div>

<div class="admin-table-card">
    <div class="card-header"><h3>Remarks & Study Material</h3></div>
    <div class="table-responsive"><table class="table table-bordered table-striped table-hover">
        <thead><tr><th>Date</th><th>Remark</th><th>Type</th><th>Teacher</th></tr></thead>
        <tbody>@foreach($remarks as $row)<tr><td>{{ $row->remark_date }}</td><td>{{ $row->remark }}</td><td>{{ ucfirst($row->remark_type) }}</td><td>{{ $row->teacher->user->name ?? '-' }}</td></tr>@endforeach</tbody>
    </table></div>
    <div class="table-responsive mt-3"><table class="table table-bordered table-striped table-hover">
        <thead><tr><th>Material</th><th>Type</th><th>Batch</th><th>Subject</th></tr></thead>
        <tbody>@foreach($studyMaterials as $row)<tr><td>{{ $row->title }}</td><td>{{ $row->material_type }}</td><td>{{ $row->batch->name ?? '-' }}</td><td>{{ $row->subject->name ?? '-' }}</td></tr>@endforeach</tbody>
    </table></div>
</div>

@if($scope['is_teacher'])
<div class="admin-table-card">
    <div class="card-header"><h3>Teacher Timetable, Logs & Salary</h3></div>
    <div class="table-responsive"><table class="table table-bordered table-striped table-hover">
        <thead><tr><th>Batch</th><th>Subject</th><th>Day/Date</th><th>Time</th><th>Status</th></tr></thead>
        <tbody>@foreach($teacherTimetables as $row)<tr><td>{{ $row->batch->name ?? '-' }}</td><td>{{ $row->subject->name ?? '-' }}</td><td>{{ $row->schedule_date ?? $row->day_of_week }}</td><td>{{ $row->start_time }} - {{ $row->end_time }}</td><td>{{ ucfirst($row->status) }}</td></tr>@endforeach</tbody>
    </table></div>
    <div class="table-responsive mt-3"><table class="table table-bordered table-striped table-hover">
        <thead><tr><th>Date</th><th>Batch</th><th>Topic</th><th>Payable Minutes</th><th>Approval</th></tr></thead>
        <tbody>@foreach($facultyLogs as $row)<tr><td>{{ $row->lecture_date }}</td><td>{{ $row->batch->name ?? '-' }}</td><td>{{ $row->topic_taught ?? '-' }}</td><td>{{ $row->salary_minutes }}</td><td>{{ ucfirst($row->approval_status) }}</td></tr>@endforeach</tbody>
    </table></div>
    <div class="table-responsive mt-3"><table class="table table-bordered table-striped table-hover">
        <thead><tr><th>Month</th><th>Regular Minutes</th><th>Extra Minutes</th><th>Net Salary</th><th>Status</th></tr></thead>
        <tbody>@foreach($salaryPayments as $row)<tr><td>{{ $row->salary_month }}</td><td>{{ $row->total_payable_regular_minutes }}</td><td>{{ $row->approved_extra_class_minutes }}</td><td>{{ $row->net_salary }}</td><td>{{ ucfirst($row->payment_status) }}</td></tr>@endforeach</tbody>
    </table></div>
</div>
@endif
@endsection
