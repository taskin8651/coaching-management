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
        <div class="action-row">
            @can('timetable_substitute')
                <a href="{{ route('admin.timetable-substitutions.index') }}" class="btn-ghost">
                    <i class="fas fa-user-clock"></i>
                    Substitute Teachers
                </a>
            @endcan

            <a href="{{ route('admin.timetables.create') }}" class="btn-primary">
                <i class="fas fa-plus"></i>
                Add Timetable
            </a>
        </div>
    @else
        @can('timetable_substitute')
            <a href="{{ route('admin.timetable-substitutions.index') }}" class="btn-primary">
                <i class="fas fa-user-clock"></i>
                Substitute Teachers
            </a>
        @endcan
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
                    <th>Course</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Day / Date</th>
                    <th>Time</th>
                    <th>Room</th>
                    <th>Status</th>
                    <th style="text-align:right;">Substitute</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($timetables as $item)
                    <tr>
                        <td>
                            <p class="table-main-text">{{ $item->course->name ?? '-' }}</p>
                            <p class="table-sub-text">course</p>
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
                   data-substitution-date
                   class="field-input"
                   style="width:135px; min-height:38px;">

            <select name="substitute_teacher_id"
                    required
                    data-free-teacher-select
                    data-timetable-id="{{ $item->id }}"
                    class="field-input"
                    style="width:170px; min-height:38px;">
                <option value="">Select date first</option>
            </select>

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

                        <td>
                            <div class="action-row">
                                @can('timetable_edit')
                                    <a class="btn-outline btn-outline-edit" href="{{ route('admin.timetables.edit', $item->id) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('timetable_delete')
                                    <form method="POST"
                                          action="{{ route('admin.timetables.destroy', $item->id) }}"
                                          style="display:inline;"
                                          onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @csrf
                                        @method('DELETE')

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

@if($isStudent)

    <div class="timetable-card my-4">

        <div class="timetable-card-body">

            <div class="timetable-table-wrap">

                <table class="timetable-grid">
                    <thead>
                        <tr>
                            <th class="teacher-name-cell">
                                My Timetable
                            </th>

                            @foreach($days as $dayKey => $dayLabel)
                                <th>
                                    {{ $dayLabel }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($timeSlots as $slot)

                            @php
                                [$startTime, $endTime] = explode('-', $slot);

                                $startTimeFormat = date('H:i', strtotime($startTime));
                                $endTimeFormat   = date('H:i', strtotime($endTime));
                            @endphp

                            <tr>
                                <th class="time-cell">
                                    {{ date('g:i A', strtotime($startTime)) }}
                                    -
                                    {{ date('g:i A', strtotime($endTime)) }}
                                </th>

                                @foreach($days as $dayKey => $dayLabel)

                                    @php
                                        $class = $timetables->first(function ($item) use ($dayKey, $startTimeFormat, $endTimeFormat) {
                                            return strtolower(trim($item->day_of_week)) == strtolower(trim($dayKey))
                                                && date('H:i', strtotime($item->start_time)) == $startTimeFormat
                                                && date('H:i', strtotime($item->end_time)) == $endTimeFormat;
                                        });
                                    @endphp

                                    <td>
                                        @if($class)
                                            <div class="class-box">
                                                <strong>{{ $class->subject->name ?? $class->batch->name ?? '' }}</strong>

                                                @if($class->teacher && $class->teacher->user)
                                                    <span>{{ $class->teacher->user->name }}</span>
                                                @endif

                                                @if($class->room)
                                                    <small>Room: {{ $class->room }}</small>
                                                @endif
                                            </div>
                                        @endif
                                    </td>

                                @endforeach
                            </tr>

                        @endforeach
                    </tbody>
                </table>

            </div>

        </div>

    </div>

@else

    @foreach($teacherWiseTimetables as $teacherId => $teacherTimetables)

        @php
            $firstTimetable = $teacherTimetables->first();
            $teacherName = $firstTimetable->teacher->user->name ?? 'No Teacher';
        @endphp

        <div class="timetable-card my-4">

            <div class="timetable-card-body">

                <div class="timetable-table-wrap">

                    <table class="timetable-grid">
                        <thead>
                            <tr>
                                <th class="teacher-name-cell">
                                    {{ $teacherName }}
                                </th>

                                @foreach($days as $dayKey => $dayLabel)
                                    <th>
                                        {{ $dayLabel }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($timeSlots as $slot)

                                @php
                                    [$startTime, $endTime] = explode('-', $slot);

                                    $startTimeFormat = date('H:i', strtotime($startTime));
                                    $endTimeFormat   = date('H:i', strtotime($endTime));
                                @endphp

                                <tr>
                                    <th class="time-cell">
                                        {{ date('g:i A', strtotime($startTime)) }}
                                        -
                                        {{ date('g:i A', strtotime($endTime)) }}
                                    </th>

                                    @foreach($days as $dayKey => $dayLabel)

                                        @php
                                            $class = $teacherTimetables->first(function ($item) use ($dayKey, $startTimeFormat, $endTimeFormat) {
                                                return strtolower(trim($item->day_of_week)) == strtolower(trim($dayKey))
                                                    && date('H:i', strtotime($item->start_time)) == $startTimeFormat
                                                    && date('H:i', strtotime($item->end_time)) == $endTimeFormat;
                                            });
                                        @endphp

                                        <td>
                                            @if($class)
                                                <div class="class-box">
                                                    <strong>{{ $class->course->name ?? '' }}</strong>

                                                    @if($class->subject)
                                                        <span>{{ $class->subject->name }}</span>
                                                    @endif

                                                    @if($class->room)
                                                        <small>Room: {{ $class->room }}</small>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>

                                    @endforeach
                                </tr>

                            @endforeach
                        </tbody>
                    </table>

                </div>

            </div>

        </div>

    @endforeach

@endif

<style>
    .timetable-card {
    background: #ffffff;
    border: 1px solid #d1d5db;
    border-radius: 0;
    overflow: hidden;
}

.timetable-card-body {
    padding: 0;
}

.timetable-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.timetable-grid {
    width: 100%;
    min-width: 850px;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    margin: 0;
    background: #ffffff;
    table-layout: fixed;
    border: 1px solid #111827 !important;
}

.timetable-grid th,
.timetable-grid td {
    border: 1px solid #111827 !important;
    padding: 8px 10px;
    height: 52px;
    min-width: 120px;
    text-align: center;
    vertical-align: middle;
    font-size: 14px;
    line-height: 1.25;
    color: #111827;
    background: #ffffff;
}

.timetable-grid thead th {
    background: #f8fafc !important;
    font-weight: 700;
    color: #000000;
}

.timetable-grid .teacher-name-cell {
    width: 150px;
    background: #ffffff !important;
    font-weight: 700;
    text-align: left;
}

.timetable-grid .time-cell {
    width: 150px;
    background: #ffffff !important;
    font-weight: 700;
    text-align: left;
    white-space: nowrap;
}

.class-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    min-height: 34px;
}

.class-box strong {
    display: block;
    font-size: 14px;
    font-weight: 700;
    color: #111827;
}

.class-box span {
    display: block;
    font-size: 12px;
    color: #475569;
}

.class-box small {
    display: block;
    font-size: 11px;
    color: #64748b;
}

@media (max-width: 768px) {
    .timetable-grid {
        min-width: 760px;
    }

    .timetable-grid th,
    .timetable-grid td {
        font-size: 13px;
        padding: 7px 8px;
        min-width: 110px;
    }

    .timetable-grid .teacher-name-cell,
    .timetable-grid .time-cell {
        width: 135px;
    }
}
</style>

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

@can('timetable_substitute')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const endpoint = @json(route('admin.timetable-substitutions.free-teachers'));

    document.querySelectorAll('[data-free-teacher-select]').forEach(function (select) {
        const form = select.closest('form');
        const dateInput = form.querySelector('[data-substitution-date]');

        function setPlaceholder(text) {
            select.innerHTML = '';
            const option = document.createElement('option');
            option.value = '';
            option.textContent = text;
            select.appendChild(option);
        }

        function loadTeachers() {
            if (! dateInput.value) {
                setPlaceholder('Select date first');
                return;
            }

            setPlaceholder('Loading...');

            const url = new URL(endpoint);
            url.searchParams.set('timetable_id', select.dataset.timetableId);
            url.searchParams.set('substitution_date', dateInput.value);

            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(response => response.json())
                .then(data => {
                    select.innerHTML = '';

                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = data.teachers && data.teachers.length ? 'Select Teacher' : 'No free teacher';
                    select.appendChild(defaultOption);

                    (data.teachers || []).forEach(function (teacher) {
                        const option = document.createElement('option');
                        option.value = teacher.id;
                        option.textContent = teacher.name;
                        select.appendChild(option);
                    });
                })
                .catch(() => setPlaceholder('Unable to load'));
        }

        dateInput.addEventListener('change', loadTeachers);
    });
});
</script>
@endcan
@endsection
