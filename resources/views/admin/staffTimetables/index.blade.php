@extends('layouts.admin')

@section('page-title', 'Staff Duty Timetable')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Staff Duty Timetable</h2>
        <p class="admin-page-subtitle">
            Which staff member is on duty, for how long and at what time
        </p>
    </div>

    @can('staff_timetable_create')
        <a href="{{ route('admin.staff-timetables.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Duty Schedule
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Schedules</p>
        <p class="stat-value">{{ $timetables->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Scheduled</p>
        <p class="stat-value">{{ $timetables->where('status', 'scheduled')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Cancelled</p>
        <p class="stat-value">{{ $timetables->where('status', 'cancelled')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Staff Covered</p>
        <p class="stat-value">{{ $staffWiseTimetables->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Duty Schedules</p>

        <span class="page-card-note">
            <i class="fas fa-clock"></i>
            Day/date, timing and location per staff
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-StaffTimetable">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Staff</th>
                    <th>Branch</th>
                    <th>Day / Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($timetables as $item)
                    <tr data-entry-id="{{ $item->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $staffName = $item->staff->user->name ?? 'Staff';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$item->id % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($staffName, 0, 1)) }}
                                </div>

                                <p class="table-main-text">{{ $staffName }}</p>
                            </div>
                        </td>

                        <td>
                            {{ $item->branch->name ?? '-' }}
                        </td>

                        <td>
                            @if($item->schedule_date)
                                <p class="table-main-text">{{ \Carbon\Carbon::parse($item->schedule_date)->format('d M Y') }}</p>
                                <p class="table-sub-text">{{ $item->day_of_week ?? \Carbon\Carbon::parse($item->schedule_date)->format('l') }}</p>
                            @else
                                <p class="table-main-text">{{ $item->day_of_week ?? '-' }}</p>
                                <p class="table-sub-text">Weekly Schedule</p>
                            @endif
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ $item->start_time }} - {{ $item->end_time }}
                            </span>
                        </td>

                        <td>
                            {{ $item->location ?? '-' }}
                        </td>

                        <td>
                            @if($item->status == 'scheduled')
                                <span class="status-pill success">Scheduled</span>
                            @else
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('staff_timetable_edit')
                                    <a href="{{ route('admin.staff-timetables.edit', $item->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('staff_timetable_delete')
                                    <form action="{{ route('admin.staff-timetables.destroy', $item->id) }}"
                                          method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @method('DELETE')
                                        @csrf

                                        <button type="submit" class="btn-outline btn-outline-danger">
                                            <i class="fas fa-trash-alt"></i>
                                            Delete
                                        </button>
                                    </form>
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
    initAdminDataTable('.datatable-StaffTimetable', {
        canDelete: @can('staff_timetable_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.staff-timetables.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search duty schedules...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ schedules'
    });
});
</script>
@endsection
