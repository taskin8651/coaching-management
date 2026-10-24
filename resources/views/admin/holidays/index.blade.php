
@extends('layouts.admin')

@section('page-title', 'Holidays')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Holiday Calendar</h2>
        <p class="admin-page-subtitle">
            Manage institute, branch, student, teacher and staff holidays
        </p>
    </div>

    @can('holiday_create')
        <a href="{{ route('admin.holidays.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Holiday
        </a>
    @endcan
</div>

{{-- Statistics --}}
<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Holidays</p>
        <p class="stat-value">{{ $holidays->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Mandatory</p>
        <p class="stat-value">
            {{ $holidays->where('type', 'mandatory')->count() }}
        </p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Optional</p>
        <p class="stat-value">
            {{ $holidays->where('type', 'optional')->count() }}
        </p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Upcoming Holidays</p>
        <p class="stat-value">
            {{ $holidays->filter(function ($holiday) {
                return $holiday->end_date
                    && $holiday->end_date->gte(now()->startOfDay());
            })->count() }}
        </p>
    </div>
</div>

{{-- Holiday Table --}}
<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Holidays</p>

        <span class="page-card-note">
            <i class="fas fa-calendar-alt"></i>
            Institute holiday records
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Holiday">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Holiday Details</th>
                    <th>From - To</th>
                    <th>Duration</th>
                    <th>Branch</th>
                    <th>Type</th>
                    <th style="text-align:right;">
                        {{ trans('global.actions') }}
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($holidays as $holiday)
                    @php
                        $canManage = $scope['is_admin']
                            || (
                                $scope['is_manager']
                                && $holiday->branch_id
                                && $holiday->branch_id == $scope['branch_id']
                            );

                        $colors = [
                            '#4F46E5',
                            '#0EA5E9',
                            '#10B981',
                            '#F59E0B',
                            '#EF4444',
                            '#8B5CF6',
                            '#EC4899',
                            '#14B8A6'
                        ];

                        $color = $colors[$holiday->id % count($colors)];

                        $duration = $holiday->start_date && $holiday->end_date
                            ? $holiday->start_date->diffInDays($holiday->end_date) + 1
                            : 0;
                    @endphp

                    <tr data-entry-id="{{ $holiday->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">
                                #{{ $loop->iteration }}
                            </span>
                        </td>

                        {{-- Holiday Name --}}
                        <td>
                            <div class="inline-flex-center">
                                <div class="avatar-circle"
                                     style="background: {{ $color }};">
                                    <i class="fas fa-calendar-day"></i>
                                </div>

                                <div>
                                    <p class="table-main-text">
                                        {{ $holiday->name }}
                                    </p>

                                    <p class="table-sub-text">
                                        @if($holiday->created_at)
                                            Added
                                            {{ $holiday->created_at->format('d M Y') }}
                                        @else
                                            Holiday
                                        @endif
                                    </p>

                                    @if($holiday->description)
                                        <p class="table-sub-text">
                                            {{ \Illuminate\Support\Str::limit($holiday->description, 65) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Date Range --}}
                        <td>
                            <span style="font-size:12.5px; color:#374151;">
                                {{ optional($holiday->start_date)->format('d M Y') ?? '-' }}
                            </span>

                            @if(
                                $holiday->start_date
                                && $holiday->end_date
                                && !$holiday->start_date->isSameDay($holiday->end_date)
                            )
                                <div class="table-sub-text">
                                    to {{ $holiday->end_date->format('d M Y') }}
                                </div>
                            @endif
                        </td>

                        {{-- Duration --}}
                        <td>
                            <span class="status-pill">
                                {{ $duration ? $duration . ' day(s)' : '-' }}
                            </span>
                        </td>

                        {{-- Branch --}}
                        <td>
                            @if($holiday->branch_id)
                                <span class="table-main-text">
                                    {{ $holiday->branch->name ?? 'Branch #' . $holiday->branch_id }}
                                </span>
                            @else
                                <span class="status-pill">
                                    All Branches
                                </span>
                            @endif
                        </td>

                        {{-- Holiday Type --}}
                        <td>
                            @if($holiday->type === 'mandatory')
                                <span class="status-pill success">
                                    Mandatory
                                </span>
                            @else
                                <span class="status-pill warning">
                                    Optional
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td>
                            <div class="action-row">
                                @can('holiday_show')
                                    <a href="{{ route('admin.holidays.show', $holiday->id) }}"
                                       class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @if($canManage)
                                    @can('holiday_edit')
                                        <a href="{{ route('admin.holidays.edit', $holiday->id) }}"
                                           class="btn-outline btn-outline-edit">
                                            <i class="fas fa-pencil-alt"></i>
                                            Edit
                                        </a>
                                    @endcan

                                    @can('holiday_delete')
                                        <form action="{{ route('admin.holidays.destroy', $holiday->id) }}"
                                              method="POST"
                                              style="display:inline;"
                                              onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="btn-outline btn-outline-danger">
                                                <i class="fas fa-trash-alt"></i>
                                                Delete
                                            </button>
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

@endsection

@section('scripts')
@parent

<script>
$(function () {
    initAdminDataTable('.datatable-Holiday', {
        canDelete: @can('holiday_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.holidays.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search holidays...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ holidays'
    });
});
</script>
@endsection
