@extends('layouts.admin')

@section('page-title', 'Teacher & Staff Attendance')

@section('content')
<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Teacher & Staff Attendance</h2>
        <p class="admin-page-subtitle">Teacher attendance is batch-wise; staff attendance is branch-wise.</p>
    </div>
    @can('staff_create')
        <a href="{{ route('admin.staff-attendances.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Mark Attendance</a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card"><p class="stat-label">Total Records</p><p class="stat-value">{{ $attendances->count() }}</p></div>
    <div class="stat-card"><p class="stat-label">Teachers</p><p class="stat-value">{{ $attendances->whereNotNull('teacher_id')->count() }}</p></div>
    <div class="stat-card"><p class="stat-label">Staff</p><p class="stat-value">{{ $attendances->whereNotNull('staff_id')->count() }}</p></div>
    <div class="stat-card"><p class="stat-label">Present</p><p class="stat-value">{{ $attendances->where('status', 'present')->count() }}</p></div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">Attendance Records</p>
        <span class="page-card-note"><i class="fas fa-fingerprint"></i> Manual or biometric source</span>
    </div>
    <div class="page-card-table">
        <table class="min-w-full datatable datatable-StaffAttendance">
            <thead><tr><th>Date</th><th>Person</th><th>Type</th><th>Batch / Branch</th><th>Time</th><th>Worked</th><th>Status</th><th>Source</th></tr></thead>
            <tbody>
                @foreach($attendances as $attendance)
                    @php
                        $isTeacher = (bool) $attendance->teacher_id;
                        $person = $isTeacher ? ($attendance->teacher->user->name ?? 'Teacher') : ($attendance->staff->user->name ?? 'Staff');
                    @endphp
                    <tr>
                        <td><p class="table-main-text">{{ optional($attendance->attendance_date)->format('d M Y') ?? '-' }}</p></td>
                        <td><p class="table-main-text">{{ $person }}</p><p class="table-sub-text">{{ $attendance->branch->name ?? '-' }}</p></td>
                        <td><span class="status-pill {{ $isTeacher ? 'warning' : 'success' }}">{{ $isTeacher ? 'Teacher' : 'Staff' }}</span></td>
                        <td>
                            @if($isTeacher)
                                <p class="table-main-text">{{ $attendance->batch->name ?? '-' }}</p><p class="table-sub-text">Assigned Batch</p>
                            @else
                                <p class="table-main-text">{{ $attendance->branch->name ?? '-' }}</p><p class="table-sub-text">Branch</p>
                            @endif
                        </td>
                        <td><span class="code-pill">{{ $attendance->first_in_time ?? '-' }} - {{ $attendance->last_out_time ?? '-' }}</span></td>
                        <td><p class="table-main-text">{{ $attendance->worked_minutes }} min</p></td>
                        <td><span class="status-pill {{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : '') }}">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</span></td>
                        <td><span class="status-pill" style="background:#EDE9FE;color:#6D28D9;">{{ ucfirst($attendance->source) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>$(function () { initAdminDataTable('.datatable-StaffAttendance', { searchPlaceholder: 'Search attendance...', infoText: 'Showing _START_–_END_ of _TOTAL_ attendance records' }); });</script>
@endsection
