@extends('layouts.admin')
@section('page-title', 'Student Attendance')
@section('content')
<div class="admin-page-head"><div><h2 class="admin-page-title">Student Attendance</h2><p class="admin-page-subtitle">Batch-wise attendance records</p></div>@can('student_attendance_create')<a href="{{ route('admin.student-attendances.create') }}" class="btn-primary"><i class="fas fa-plus"></i> Mark Attendance</a>@endcan</div>
<div class="page-card"><div class="page-card-table"><table class="min-w-full datatable datatable-StudentAttendance"><thead><tr><th>Date</th><th>Student</th><th>Batch</th><th>Subject</th><th>Scheduled</th><th>Actual</th><th>Status</th><th>Source</th></tr></thead><tbody>
@foreach($attendances as $attendance)<tr><td>{{ $attendance->attendance_date ? $attendance->attendance_date->format('d M Y') : '-' }}</td><td>{{ $attendance->student->user->name ?? '-' }}</td><td>{{ $attendance->batch->name ?? '-' }}</td><td>{{ $attendance->subject->name ?? '-' }}</td><td>{{ $attendance->scheduled_start_time }} - {{ $attendance->scheduled_end_time }}</td><td>{{ $attendance->actual_in_time ?? '-' }} - {{ $attendance->actual_out_time ?? '-' }}</td><td><span class="status-pill {{ in_array($attendance->status, ['present']) ? 'success' : ($attendance->status === 'absent' ? 'danger' : 'warning') }}">{{ ucfirst($attendance->status) }}</span></td><td>{{ ucfirst($attendance->source) }}</td></tr>@endforeach
</tbody></table></div></div>
@endsection
@section('scripts')@parent<script>$(function(){initAdminDataTable('.datatable-StudentAttendance',{searchPlaceholder:'Search attendance...'});});</script>@endsection
