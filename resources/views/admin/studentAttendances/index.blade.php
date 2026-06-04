@extends('layouts.admin')

@section('page-title', 'Student Attendance')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Student Attendance</h2>
        <p class="admin-page-subtitle">
            Batch-wise student attendance records, timing and biometric/manual source
        </p>
    </div>

    @can('student_attendance_create')
        <a href="{{ route('admin.student-attendances.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Mark Attendance
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Records</p>
        <p class="stat-value">{{ $attendances->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Present</p>
        <p class="stat-value">{{ $attendances->where('status', 'present')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Absent</p>
        <p class="stat-value">{{ $attendances->where('status', 'absent')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Late / Other</p>
        <p class="stat-value">
            {{ $attendances->filter(fn($row) => !in_array($row->status, ['present', 'absent']))->count() }}
        </p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Student Attendance</p>

        <span class="page-card-note">
            <i class="fas fa-user-check"></i>
            Attendance records from manual or biometric source
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-StudentAttendance">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Batch</th>
                    <th>Subject</th>
                    <th>Scheduled</th>
                    <th>Actual</th>
                    <th>Status</th>
                    <th>Source</th>
                </tr>
            </thead>

            <tbody>
                @foreach($attendances as $attendance)
                    <tr>
                        <td>
                            <p class="table-main-text">
                                {{ $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') : '-' }}
                            </p>
                            <p class="table-sub-text">Attendance Date</p>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $name = $attendance->student->user->name ?? 'Student';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $name }}</p>
                                    <p class="table-sub-text">{{ $attendance->student->student_code ?? 'Student' }}</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <p class="table-main-text">{{ $attendance->batch->name ?? '-' }}</p>
                            <p class="table-sub-text">Batch</p>
                        </td>

                        <td>
                            <p class="table-main-text">{{ $attendance->subject->name ?? '-' }}</p>
                            <p class="table-sub-text">Subject</p>
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ $attendance->scheduled_start_time ?? '-' }} - {{ $attendance->scheduled_end_time ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ $attendance->actual_in_time ?? '-' }} - {{ $attendance->actual_out_time ?? '-' }}
                            </span>
                        </td>

                        <td>
                            @if($attendance->status === 'present')
                                <span class="status-pill success">Present</span>
                            @elseif($attendance->status === 'absent')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Absent</span>
                            @elseif($attendance->status === 'late')
                                <span class="status-pill warning">Late</span>
                            @elseif($attendance->status === 'half_day')
                                <span class="status-pill warning">Half Day</span>
                            @elseif($attendance->status === 'leave')
                                <span class="status-pill" style="background:#DBEAFE;color:#1D4ED8;">Leave</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($attendance->status ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($attendance->source === 'biometric')
                                <span class="status-pill" style="background:#EDE9FE;color:#6D28D9;">
                                    <i class="fas fa-fingerprint"></i>
                                    Biometric
                                </span>
                            @elseif($attendance->source === 'manual')
                                <span class="status-pill" style="background:#ECFDF5;color:#047857;">
                                    <i class="fas fa-user-edit"></i>
                                    Manual
                                </span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($attendance->source ?? '-') }}
                                </span>
                            @endif
                        </td>
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
$(function () {
    initAdminDataTable('.datatable-StudentAttendance', {
        searchPlaceholder: 'Search attendance...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ attendance records'
    });
});
</script>
@endsection