@extends('layouts.admin')

@section('page-title', 'Events')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Events & Workshops</h2>
        <p class="admin-page-subtitle">
            Workshops, trips, seminars and other special programs — enrollment and fee collection separate from regular batch fees
        </p>
    </div>

    @can('event_create')
        <a href="{{ route('admin.events.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Event
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Events</p>
        <p class="stat-value">{{ $events->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Open for Enrollment</p>
        <p class="stat-value">{{ $events->where('status', 'open')->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Draft</p>
        <p class="stat-value">{{ $events->where('status', 'draft')->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Total Enrollments</p>
        <p class="stat-value">{{ $events->sum('enrollments_count') }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Events</p>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Event">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Event</th>
                    <th>Branch</th>
                    <th>Dates</th>
                    <th>Enrolled</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($events as $event)
                    <tr data-entry-id="{{ $event->id }}">
                        <td></td>
                        <td><span class="id-text">#{{ $loop->iteration }}</span></td>
                        <td>
                            <p class="table-main-text">{{ $event->name }}</p>
                            <p class="table-sub-text">{{ $event->code }} {{ $event->event_type ? '• ' . $event->event_type : '' }}</p>
                        </td>
                        <td>{{ $event->branch->name ?? 'Multi-Branch' }}</td>
                        <td>{{ optional($event->start_date)->format('d M Y') }}{{ $event->end_date ? ' — ' . $event->end_date->format('d M Y') : '' }}</td>
                        <td>{{ $event->enrollments_count }}</td>
                        <td>
                            @if($event->status == 'open')
                                <span class="status-pill success">Open</span>
                            @elseif($event->status == 'closed')
                                <span class="status-pill warning">Closed</span>
                            @elseif($event->status == 'cancelled')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                            @else
                                <span class="status-pill">Draft</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-row">
                                @can('event_show')
                                    <a href="{{ route('admin.events.show', $event->id) }}" class="btn-outline"><i class="fas fa-eye"></i> View</a>
                                @endcan
                                @can('event_edit')
                                    <a href="{{ route('admin.events.edit', $event->id) }}" class="btn-outline btn-outline-edit"><i class="fas fa-pencil-alt"></i> Edit</a>
                                @endcan
                                @can('event_delete')
                                    <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn-outline btn-outline-danger"><i class="fas fa-trash-alt"></i> Delete</button>
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
    initAdminDataTable('.datatable-Event', {
        canDelete: @can('event_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.events.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search events...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ events'
    });
});
</script>
@endsection
