@extends('layouts.admin')

@section('page-title', 'Timetables')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Timetables</h2>
        <p class="admin-page-subtitle">
            Batch, subject, faculty schedule and substitute teacher management
        </p>
    </div>

    @can('timetable_create')
        <a href="{{ route('admin.timetables.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Timetable
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Timetables</p>
        <p class="stat-value">{{ $timetables->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $timetables->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Cancelled</p>
        <p class="stat-value">{{ $timetables->where('status', 'cancelled')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Completed</p>
        <p class="stat-value">{{ $timetables->where('status', 'completed')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Timetables</p>

        <span class="page-card-note">
            <i class="fas fa-calendar-alt"></i>
            Manage regular classes and substitute assignments
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Timetables">
            <thead>
                <tr>
                    <th>Batch</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Day / Date</th>
                    <th>Time</th>
                    <th>Room</th>
                    <th>Status</th>
                    <th style="text-align:right;">Substitute</th>
                </tr>
            </thead>

            <tbody>
                @foreach($timetables as $item)
                    <tr>
                        <td>
                            <p class="table-main-text">{{ $item->batch->name ?? '-' }}</p>
                            <p class="table-sub-text">Batch</p>
                        </td>

                        <td>
                            <p class="table-main-text">{{ $item->subject->name ?? '-' }}</p>
                            <p class="table-sub-text">Subject</p>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $teacherName = $item->teacher->user->name ?? 'Teacher';
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
                            @if($item->schedule_date)
                                <p class="table-main-text">
                                    {{ \Carbon\Carbon::parse($item->schedule_date)->format('d M Y') }}
                                </p>
                                <p class="table-sub-text">
                                    {{ $item->day_of_week ?? \Carbon\Carbon::parse($item->schedule_date)->format('l') }}
                                </p>
                            @else
                                <p class="table-main-text">{{ $item->day_of_week ?? '-' }}</p>
                                <p class="table-sub-text">Weekly Schedule</p>
                            @endif
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ $item->start_time ?? '-' }} - {{ $item->end_time ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ $item->room ?? '-' }}
                            </span>
                        </td>

                        <td>
                            @if($item->status == 'active')
                                <span class="status-pill success">Active</span>
                            @elseif($item->status == 'completed')
                                <span class="status-pill success">Completed</span>
                            @elseif($item->status == 'cancelled')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                            @elseif($item->status == 'inactive')
                                <span class="status-pill warning">Inactive</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($item->status ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @can('timetable_substitute')
                                <form method="POST"
                                      action="{{ route('admin.timetables.substitute', $item->id) }}"
                                      class="action-row"
                                      style="justify-content:flex-end; gap:8px;">
                                    @csrf

                                    <input type="date"
                                           name="substitution_date"
                                           required
                                           class="field-input"
                                           style="width:135px; min-height:38px;">

                                    <input type="number"
                                           name="substitute_teacher_id"
                                           placeholder="Teacher ID"
                                           required
                                           class="field-input"
                                           style="width:100px; min-height:38px;">

                                    <button type="submit" class="btn-outline">
                                        <i class="fas fa-user-plus"></i>
                                        Assign
                                    </button>
                                </form>
                            @else
                                <div class="action-row">
                                    <span style="font-size:12px;color:#94A3B8;">—</span>
                                </div>
                            @endcan
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
    initAdminDataTable('.datatable-Timetables', {
        searchPlaceholder: 'Search timetables...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ timetables'
    });
});
</script>
@endsection