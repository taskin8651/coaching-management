@extends('layouts.admin')

@section('page-title', 'Holidays')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Holiday Calendar</h2>
        <p class="admin-page-subtitle">
            Institute and branch holidays — mandatory holidays reduce the working-days count used in salary calculation
        </p>
    </div>

    @can('holiday_create')
        <a href="{{ route('admin.holidays.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Holiday
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Holidays</p>
        <p class="stat-value">{{ $holidays->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Mandatory</p>
        <p class="stat-value">{{ $holidays->where('type', 'mandatory')->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Optional</p>
        <p class="stat-value">{{ $holidays->where('type', 'optional')->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">This Month</p>
        <p class="stat-value">{{ $holidays->filter(fn ($h) => $h->date && $h->date->isSameMonth(now()))->count() }}</p>
    </div>
</div>

<div class="page-card mb-3">
    <div class="page-card-header">
        <p class="page-card-title">All Holidays</p>
        <div class="view-toggle" id="holidayViewToggle">
            <button type="button" class="view-toggle-btn" data-view="list"><i class="fas fa-list"></i> List</button>
            <button type="button" class="view-toggle-btn" data-view="calendar"><i class="fas fa-calendar-alt"></i> Calendar</button>
        </div>
    </div>

    <div class="detail-section-body">
        <form method="GET" action="{{ route('admin.holidays.index') }}" class="d-flex" style="gap:12px;flex-wrap:wrap;align-items:end;">
            <input type="hidden" name="view" id="holidayViewField" value="{{ request('view', 'list') }}">
            @if($scope['is_admin'])
                <div class="field-group mb-0" style="min-width:200px;">
                    <label class="field-label">Branch</label>
                    <select name="branch_id" class="field-input">
                        <option value="">All</option>
                        @foreach($branches as $id => $branch)
                            <option value="{{ $id }}" {{ request('branch_id') == $id ? 'selected' : '' }}>{{ $branch }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="field-group mb-0" style="min-width:200px;">
                <label class="field-label">Month</label>
                <input type="month" name="month" value="{{ request('month') }}" class="field-input">
            </div>
            <button type="submit" class="btn-outline"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.holidays.index') }}" class="btn-ghost">Clear</a>
        </form>
    </div>
</div>

<div id="holidayListView" class="page-card">
    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Holiday">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Name</th>
                    <th>Branch</th>
                    <th>Type</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($holidays as $holiday)
                    @php
                        $canManage = $scope['is_admin'] || ($scope['is_manager'] && $holiday->branch_id && $holiday->branch_id == $scope['branch_id']);
                    @endphp
                    <tr data-entry-id="{{ $holiday->id }}">
                        <td></td>
                        <td><span class="id-text">#{{ $loop->iteration }}</span></td>
                        <td>{{ optional($holiday->date)->format('d M Y') }}</td>
                        <td>{{ optional($holiday->date)->format('l') }}</td>
                        <td>
                            <p class="table-main-text">{{ $holiday->name }}</p>
                            @if($holiday->description)
                                <p class="table-sub-text">{{ \Illuminate\Support\Str::limit($holiday->description, 40) }}</p>
                            @endif
                        </td>
                        <td>
                            @if($holiday->branch_id)
                                {{ $holiday->branch->name ?? '-' }}
                            @else
                                <span class="status-pill">All Branches</span>
                            @endif
                        </td>
                        <td>
                            @if($holiday->type == 'mandatory')
                                <span class="status-pill success">Mandatory</span>
                            @else
                                <span class="status-pill warning">Optional</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-row">
                                @can('holiday_show')
                                    <a href="{{ route('admin.holidays.show', $holiday->id) }}" class="btn-outline"><i class="fas fa-eye"></i> View</a>
                                @endcan

                                @if($canManage)
                                    @can('holiday_edit')
                                        <a href="{{ route('admin.holidays.edit', $holiday->id) }}" class="btn-outline btn-outline-edit"><i class="fas fa-pencil-alt"></i> Edit</a>
                                    @endcan
                                    @can('holiday_delete')
                                        <form action="{{ route('admin.holidays.destroy', $holiday->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn-outline btn-outline-danger"><i class="fas fa-trash-alt"></i> Delete</button>
                                        </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@php
    $calMonthStr = request('month') ?: now()->format('Y-m');
    try {
        $calMonth = \Illuminate\Support\Carbon::createFromFormat('Y-m', $calMonthStr)->startOfMonth();
    } catch (\Throwable $e) {
        $calMonth = now()->startOfMonth();
    }
    $prevMonthStr = $calMonth->copy()->subMonth()->format('Y-m');
    $nextMonthStr = $calMonth->copy()->addMonth()->format('Y-m');
    $calLinkFor = fn ($m) => route('admin.holidays.index', array_filter([
        'branch_id' => request('branch_id'),
        'month' => $m,
        'view' => 'calendar',
    ], fn ($v) => $v !== null && $v !== ''));
    $holidaysByDay = $holidays
        ->filter(fn ($h) => $h->date && $h->date->format('Y-m') === $calMonth->format('Y-m'))
        ->groupBy(fn ($h) => $h->date->day);
    $leadingBlanks = $calMonth->dayOfWeek;
@endphp

<div id="holidayCalendarView" class="page-card" style="display:none;">
    <div class="cal-header">
        <div class="cal-nav">
            <a href="{{ $calLinkFor($prevMonthStr) }}" class="btn-outline cal-nav-btn"><i class="fas fa-chevron-left"></i></a>
            <p class="cal-month-label">{{ $calMonth->format('F Y') }}</p>
            <a href="{{ $calLinkFor($nextMonthStr) }}" class="btn-outline cal-nav-btn"><i class="fas fa-chevron-right"></i></a>
        </div>
        <a href="{{ $calLinkFor(now()->format('Y-m')) }}" class="btn-ghost">Today</a>
    </div>

    <div class="cal-grid cal-grid-head">
        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $wd)
            <div class="cal-weekday">{{ $wd }}</div>
        @endforeach
    </div>

    <div class="cal-grid">
        @for($i = 0; $i < $leadingBlanks; $i++)
            <div class="cal-cell cal-cell-blank"></div>
        @endfor

        @for($day = 1; $day <= $calMonth->daysInMonth; $day++)
            @php $isToday = $calMonth->copy()->day($day)->isSameDay(now()); @endphp
            <div class="cal-cell {{ $isToday ? 'cal-cell-today' : '' }}">
                <div class="cal-cell-head">
                    <span class="cal-day-num">{{ $day }}</span>
                    @can('holiday_create')
                        <a href="{{ route('admin.holidays.create', ['date' => $calMonth->copy()->day($day)->format('Y-m-d')]) }}" class="cal-add-btn" title="Add holiday"><i class="fas fa-plus"></i></a>
                    @endcan
                </div>
                @foreach($holidaysByDay->get($day, []) as $h)
                    <a href="{{ route('admin.holidays.show', $h->id) }}" class="cal-holiday-pill {{ $h->type == 'mandatory' ? 'success' : 'warning' }}" title="{{ $h->name }}">
                        {{ \Illuminate\Support\Str::limit($h->name, 16) }}
                    </a>
                @endforeach
            </div>
        @endfor
    </div>
</div>

@endsection

@section('styles')
@parent
<style>
.view-toggle {
    display: inline-flex;
    background: #F1F5F9;
    border-radius: 10px;
    padding: 3px;
    gap: 2px;
}
.view-toggle-btn {
    border: none;
    background: transparent;
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #64748B;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .15s, color .15s;
}
.view-toggle-btn.active {
    background: #fff;
    color: var(--accent, #0855A1);
    box-shadow: 0 1px 3px rgba(15,23,42,.08);
}
.cal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 16px 20px;
    border-bottom: 1px solid #F1F5F9;
}
.cal-nav {
    display: flex;
    align-items: center;
    gap: 14px;
}
.cal-nav-btn {
    padding: 6px 10px;
}
.cal-month-label {
    font-size: 15px;
    font-weight: 700;
    color: #0F172A;
    margin: 0;
    min-width: 130px;
    text-align: center;
}
.cal-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
}
.cal-grid-head {
    border-bottom: 1px solid #F1F5F9;
}
.cal-weekday {
    padding: 10px 8px;
    text-align: center;
    font-size: 11px;
    font-weight: 700;
    color: #94A3B8;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.cal-cell {
    min-height: 92px;
    border-right: 1px solid #F1F5F9;
    border-bottom: 1px solid #F1F5F9;
    padding: 6px 6px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.cal-cell-blank {
    background: #FAFBFC;
}
.cal-cell-today {
    background: var(--accent-light, #E8F3FA);
}
.cal-cell-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.cal-day-num {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}
.cal-cell-today .cal-day-num {
    color: var(--accent, #0855A1);
}
.cal-add-btn {
    width: 18px;
    height: 18px;
    display: none;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
    background: var(--accent-light, #E8F3FA);
    color: var(--accent, #0855A1);
    font-size: 10px;
    text-decoration: none;
}
.cal-cell:hover .cal-add-btn {
    display: flex;
}
.cal-holiday-pill {
    display: block;
    font-size: 10px;
    font-weight: 600;
    padding: 3px 6px;
    border-radius: 5px;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cal-holiday-pill.success {
    background: #DCFCE7;
    color: #15803D;
}
.cal-holiday-pill.warning {
    background: #FEF3C7;
    color: #B45309;
}
@media (max-width: 768px) {
    .cal-cell { min-height: 64px; }
    .cal-weekday { font-size: 10px; padding: 6px 2px; }
}
</style>
@endsection

@section('scripts')
@parent
<script>
$(function () {
    initAdminDataTable('.datatable-Holiday', {
        canDelete: false,
        searchPlaceholder: 'Search holidays...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ holidays'
    });

    var listView = document.getElementById('holidayListView');
    var calView = document.getElementById('holidayCalendarView');
    var toggleBtns = document.querySelectorAll('.view-toggle-btn');
    var viewField = document.getElementById('holidayViewField');

    function setView(view) {
        var isCalendar = view === 'calendar';
        listView.style.display = isCalendar ? 'none' : '';
        calView.style.display = isCalendar ? '' : 'none';
        viewField.value = view;
        toggleBtns.forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.view === view);
        });
        try { localStorage.setItem('holidayView', view); } catch (e) {}
    }

    toggleBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            setView(btn.dataset.view);
        });
    });

    var initialView = new URLSearchParams(window.location.search).get('view');
    if (!initialView) {
        try { initialView = localStorage.getItem('holidayView'); } catch (e) {}
    }
    setView(initialView === 'calendar' ? 'calendar' : 'list');
});
</script>
@endsection
