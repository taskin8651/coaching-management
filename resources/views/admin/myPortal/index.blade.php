@extends('layouts.admin')

@section('page-title', 'My Portal')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">My Portal</h2>
        <p class="admin-page-subtitle">
            Personal academic, fee, attendance, study material and salary visibility
        </p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Attendance Records</p>
        <p class="stat-value">{{ $studentAttendances->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Homework</p>
        <p class="stat-value">{{ $homeworks->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Fee Installments</p>
        <p class="stat-value">{{ $feeInstallments->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Study Materials</p>
        <p class="stat-value">{{ $studyMaterials->count() }}</p>
    </div>
</div>

{{-- STUDENT ATTENDANCE --}}
<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">Student Attendance</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Your attendance records
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-StudentAttendance">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Date</th>
                    <th>Batch</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Time</th>
                </tr>
            </thead>

            <tbody>
                @foreach($studentAttendances as $row)
                    <tr>
                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $name = $row->student->user->name ?? 'Student';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $name }}</p>
                                    <p class="table-sub-text">{{ $row->student->student_code ?? '-' }}</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            {{ $row->attendance_date ? \Carbon\Carbon::parse($row->attendance_date)->format('d M Y') : '-' }}
                        </td>

                        <td>{{ $row->batch->name ?? '-' }}</td>
                        <td>{{ $row->subject->name ?? '-' }}</td>

                        <td>
                            @if($row->status == 'present')
                                <span class="status-pill success">Present</span>
                            @elseif($row->status == 'absent')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Absent</span>
                            @elseif($row->status == 'late')
                                <span class="status-pill warning">Late</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($row->status ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ $row->actual_in_time ?? '-' }} - {{ $row->actual_out_time ?? '-' }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- HOMEWORK --}}
<div class="page-card mt-4">
    <div class="page-card-header">
        <p class="page-card-title">Homework</p>

        <span class="page-card-note">
            <i class="fas fa-book-open"></i>
            Assigned homework and submission status
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Homework">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Batch</th>
                    <th>Subject</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($homeworks as $row)
                    <tr>
                        <td>
                            <p class="table-main-text">{{ $row->title }}</p>
                            <p class="table-sub-text">{{ $row->description ? \Illuminate\Support\Str::limit($row->description, 55) : 'Homework details' }}</p>
                        </td>

                        <td>{{ $row->batch->name ?? '-' }}</td>
                        <td>{{ $row->subject->name ?? '-' }}</td>

                        <td>
                            {{ $row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d M Y') : '-' }}
                        </td>

                        <td>
                            @if($row->status == 'active')
                                <span class="status-pill success">Active</span>
                            @elseif($row->status == 'completed')
                                <span class="status-pill success">Completed</span>
                            @elseif($row->status == 'pending')
                                <span class="status-pill warning">Pending</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($row->status ?? '-') }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- FEES --}}
<div class="page-card mt-4">
    <div class="page-card-header">
        <p class="page-card-title">Fees</p>

        <span class="page-card-note">
            <i class="fas fa-rupee-sign"></i>
            Fee installment and payment status
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-FeeInstallment">
            <thead>
                <tr>
                    <th>Installment</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @foreach($feeInstallments as $row)
                    <tr>
                        <td>
                            <p class="table-main-text">{{ $row->title }}</p>
                            <p class="table-sub-text">Fee installment</p>
                        </td>

                        <td>₹{{ number_format($row->amount, 2) }}</td>
                        <td><strong>₹{{ number_format($row->paid_amount, 2) }}</strong></td>
                        <td>₹{{ number_format($row->due_amount, 2) }}</td>

                        <td>
                            {{ $row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d M Y') : '-' }}
                        </td>

                        <td>
                            @if($row->status == 'paid')
                                <span class="status-pill success">Paid</span>
                            @elseif($row->status == 'partial')
                                <span class="status-pill warning">Partial</span>
                            @elseif($row->status == 'overdue')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Overdue</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($row->status ?? '-') }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- RESULTS --}}
<div class="page-card mt-4">
    <div class="page-card-header">
        <p class="page-card-title">Results</p>

        <span class="page-card-note">
            <i class="fas fa-clipboard-list"></i>
            Exam result and report card
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-ReportCard">
            <thead>
                <tr>
                    <th>Exam</th>
                    <th>Marks</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Rank</th>
                </tr>
            </thead>

            <tbody>
                @foreach($reportCards as $row)
                    <tr>
                        <td>
                            <p class="table-main-text">{{ $row->exam->title ?? '-' }}</p>
                            <p class="table-sub-text">{{ $row->exam->exam_type ?? 'Exam' }}</p>
                        </td>

                        <td>
                            <strong>{{ $row->marks_obtained }}</strong> / {{ $row->total_marks }}
                        </td>

                        <td>{{ number_format($row->percentage, 2) }}%</td>

                        <td>
                            <span class="code-pill">{{ $row->grade ?? '-' }}</span>
                        </td>

                        <td>
                            {{ $row->rank ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- REMARKS --}}
<div class="page-card mt-4">
    <div class="page-card-header">
        <p class="page-card-title">Remarks</p>

        <span class="page-card-note">
            <i class="fas fa-comment-dots"></i>
            Teacher remarks and feedback
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Remark">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Remark</th>
                    <th>Type</th>
                    <th>Teacher</th>
                </tr>
            </thead>

            <tbody>
                @foreach($remarks as $row)
                    <tr>
                        <td>
                            {{ $row->remark_date ? \Carbon\Carbon::parse($row->remark_date)->format('d M Y') : '-' }}
                        </td>

                        <td>
                            <p class="table-main-text">{{ $row->remark }}</p>
                        </td>

                        <td>
                            <span class="code-pill">{{ ucfirst($row->remark_type ?? '-') }}</span>
                        </td>

                        <td>{{ $row->teacher->user->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- STUDY MATERIAL --}}
<div class="page-card mt-4">
    <div class="page-card-header">
        <p class="page-card-title">Study Materials</p>

        <span class="page-card-note">
            <i class="fas fa-book-reader"></i>
            Notes, assignments and study files
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-StudyMaterial">
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Type</th>
                    <th>Batch</th>
                    <th>Subject</th>
                </tr>
            </thead>

            <tbody>
                @foreach($studyMaterials as $row)
                    <tr>
                        <td>
                            <p class="table-main-text">{{ $row->title }}</p>
                            <p class="table-sub-text">{{ $row->description ? \Illuminate\Support\Str::limit($row->description, 55) : 'Study material' }}</p>
                        </td>

                        <td>
                            <span class="code-pill">{{ $row->material_type }}</span>
                        </td>

                        <td>{{ $row->batch->name ?? '-' }}</td>
                        <td>{{ $row->subject->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($scope['is_teacher'])
    <div class="stats-grid mt-4">
        <div class="stat-card">
            <p class="stat-label">Timetable</p>
            <p class="stat-value">{{ $teacherTimetables->count() }}</p>
        </div>

        <div class="stat-card">
            <p class="stat-label">Faculty Logs</p>
            <p class="stat-value">{{ $facultyLogs->count() }}</p>
        </div>

        <div class="stat-card">
            <p class="stat-label">Salary Records</p>
            <p class="stat-value">{{ $salaryPayments->count() }}</p>
        </div>

        <div class="stat-card">
            <p class="stat-label">Net Salary</p>
            <p class="stat-value">₹{{ number_format($salaryPayments->sum('net_salary'), 0) }}</p>
        </div>
    </div>

    {{-- TEACHER TIMETABLE --}}
    <div class="page-card mt-4">
        <div class="page-card-header">
            <p class="page-card-title">Teacher Timetable</p>

            <span class="page-card-note">
                <i class="fas fa-calendar-alt"></i>
                Assigned class schedule
            </span>
        </div>

        <div class="page-card-table">
            <table class="min-w-full datatable datatable-TeacherTimetable">
                <thead>
                    <tr>
                        <th>Batch</th>
                        <th>Subject</th>
                        <th>Day / Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($teacherTimetables as $row)
                        <tr>
                            <td>{{ $row->batch->name ?? '-' }}</td>
                            <td>{{ $row->subject->name ?? '-' }}</td>

                            <td>
                                {{ $row->schedule_date ? \Carbon\Carbon::parse($row->schedule_date)->format('d M Y') : ($row->day_of_week ?? '-') }}
                            </td>

                            <td>
                                <span class="code-pill">
                                    {{ $row->start_time ?? '-' }} - {{ $row->end_time ?? '-' }}
                                </span>
                            </td>

                            <td>
                                @if($row->status == 'active')
                                    <span class="status-pill success">Active</span>
                                @elseif($row->status == 'cancelled')
                                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                                @else
                                    <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                        {{ ucfirst($row->status ?? '-') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- FACULTY LOGS --}}
    <div class="page-card mt-4">
        <div class="page-card-header">
            <p class="page-card-title">Faculty Logs</p>

            <span class="page-card-note">
                <i class="fas fa-chalkboard-teacher"></i>
                Class logs and payable minutes
            </span>
        </div>

        <div class="page-card-table">
            <table class="min-w-full datatable datatable-FacultyLog">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Batch</th>
                        <th>Topic</th>
                        <th>Payable Minutes</th>
                        <th>Approval</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($facultyLogs as $row)
                        <tr>
                            <td>
                                {{ $row->lecture_date ? \Carbon\Carbon::parse($row->lecture_date)->format('d M Y') : '-' }}
                            </td>

                            <td>{{ $row->batch->name ?? '-' }}</td>

                            <td>
                                <p class="table-main-text">{{ $row->topic_taught ?? '-' }}</p>
                            </td>

                            <td>
                                <span class="code-pill">{{ $row->salary_minutes ?? 0 }} min</span>
                            </td>

                            <td>
                                @if($row->approval_status == 'approved')
                                    <span class="status-pill success">Approved</span>
                                @elseif($row->approval_status == 'pending')
                                    <span class="status-pill warning">Pending</span>
                                @elseif($row->approval_status == 'rejected')
                                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Rejected</span>
                                @else
                                    <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                        {{ ucfirst($row->approval_status ?? '-') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- SALARY PAYMENTS --}}
    <div class="page-card mt-4">
        <div class="page-card-header">
            <p class="page-card-title">Salary Payments</p>

            <span class="page-card-note">
                <i class="fas fa-hand-holding-usd"></i>
                Salary visibility
            </span>
        </div>

        <div class="page-card-table">
            <table class="min-w-full datatable datatable-SalaryPayment">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Regular Minutes</th>
                        <th>Extra Minutes</th>
                        <th>Net Salary</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($salaryPayments as $row)
                        <tr>
                            <td>
                                <p class="table-main-text">{{ $row->salary_month ?? '-' }}</p>
                            </td>

                            <td>{{ $row->total_payable_regular_minutes ?? 0 }}</td>
                            <td>{{ $row->approved_extra_class_minutes ?? 0 }}</td>

                            <td>
                                <strong>₹{{ number_format($row->net_salary, 2) }}</strong>
                            </td>

                            <td>
                                @if($row->payment_status == 'paid')
                                    <span class="status-pill success">Paid</span>
                                @elseif($row->payment_status == 'partial')
                                    <span class="status-pill warning">Partial</span>
                                @elseif($row->payment_status == 'due')
                                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Due</span>
                                @else
                                    <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                        {{ ucfirst($row->payment_status ?? '-') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>
$(function () {
    $('.datatable-StudentAttendance').DataTable();
    $('.datatable-Homework').DataTable();
    $('.datatable-FeeInstallment').DataTable();
    $('.datatable-ReportCard').DataTable();
    $('.datatable-Remark').DataTable();
    $('.datatable-StudyMaterial').DataTable();

    @if($scope['is_teacher'])
        $('.datatable-TeacherTimetable').DataTable();
        $('.datatable-FacultyLog').DataTable();
        $('.datatable-SalaryPayment').DataTable();
    @endif
});
</script>
@endsection