@extends('layouts.admin')

@section('page-title', 'Faculty Log Book')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Faculty Log Book</h2>
        <p class="admin-page-subtitle">
            Payable class minutes, actual teaching time and approval status
        </p>
    </div>

    @can('faculty_log_create')
        <a href="{{ route('admin.faculty-log-books.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Log
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Logs</p>
        <p class="stat-value">{{ $logs->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Approved</p>
        <p class="stat-value">{{ $logs->where('approval_status', 'approved')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Pending</p>
        <p class="stat-value">{{ $logs->where('approval_status', 'pending')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Payable Minutes</p>
        <p class="stat-value">{{ $logs->sum('salary_minutes') }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Faculty Logs</p>

        <span class="page-card-note">
            <i class="fas fa-chalkboard-teacher"></i>
            Track teacher class logs and salary minutes
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-FacultyLogs">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Teacher</th>
                    <th>Batch</th>
                    <th>Subject</th>
                    <th>Scheduled</th>
                    <th>Actual</th>
                    <th>Payable</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>
                            <p class="table-main-text">
                                {{ $log->lecture_date ? \Carbon\Carbon::parse($log->lecture_date)->format('d M Y') : '-' }}
                            </p>
                            <p class="table-sub-text">Lecture Date</p>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $teacherName = $log->teacher->user->name ?? 'Teacher';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($teacherName, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $teacherName }}</p>
                                    <p class="table-sub-text">Faculty</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <p class="table-main-text">{{ $log->batch->name ?? '-' }}</p>
                            <p class="table-sub-text">Batch</p>
                        </td>

                        <td>
                            <p class="table-main-text">{{ $log->subject->name ?? '-' }}</p>
                            <p class="table-sub-text">Subject</p>
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ $log->scheduled_start_time ?? '-' }} - {{ $log->scheduled_end_time ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ $log->actual_start_time ?? '-' }} - {{ $log->actual_end_time ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <span class="status-pill" style="background:#EDE9FE;color:#6D28D9;">
                                {{ $log->salary_minutes ?? 0 }} min
                            </span>
                        </td>

                        <td>
                            <div style="display:flex;flex-direction:column;gap:6px;">
                                @if($log->log_status == 'completed')
                                    <span class="status-pill success">Completed</span>
                                @elseif($log->log_status == 'cancelled')
                                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                                @elseif($log->log_status == 'running')
                                    <span class="status-pill warning">Running</span>
                                @else
                                    <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                        {{ ucfirst($log->log_status ?? '-') }}
                                    </span>
                                @endif

                                @if($log->approval_status == 'approved')
                                    <span class="status-pill success">Approved</span>
                                @elseif($log->approval_status == 'rejected')
                                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Rejected</span>
                                @elseif($log->approval_status == 'pending')
                                    <span class="status-pill warning">Pending</span>
                                @else
                                    <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                        {{ ucfirst($log->approval_status ?? '-') }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td>
                            <div class="action-row">
                                @can('faculty_log_show')
                                    <a class="btn-outline" href="{{ route('admin.faculty-log-books.show', $log->id) }}">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('faculty_log_edit')
                                    <a class="btn-outline btn-outline-edit" href="{{ route('admin.faculty-log-books.edit', $log->id) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('faculty_log_approve')
                                    @if($log->approval_status !== 'approved')
                                        <form method="POST"
                                              action="{{ route('admin.faculty-log-books.approve', $log->id) }}"
                                              style="display:inline;">
                                            @csrf

                                            <button type="submit" class="btn-outline">
                                                <i class="fas fa-check"></i>
                                                Approve
                                            </button>
                                        </form>
                                    @else
                                        <span class="status-pill success">
                                            <i class="fas fa-check"></i>
                                            Done
                                        </span>
                                    @endif
                                @endcan
                            </div>
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
    initAdminDataTable('.datatable-FacultyLogs', {
        searchPlaceholder: 'Search faculty logs...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ faculty logs'
    });
});
</script>
@endsection