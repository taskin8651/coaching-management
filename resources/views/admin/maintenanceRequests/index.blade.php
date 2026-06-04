@extends('layouts.admin')

@section('page-title', 'Maintenance')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Maintenance</h2>
        <p class="admin-page-subtitle">
            Issue reporting, repair tracking and assignment management
        </p>
    </div>

    @can('maintenance_create')
        <a href="{{ route('admin.maintenance-requests.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Issue
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Issues</p>
        <p class="stat-value">{{ $maintenanceRequests->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Open</p>
        <p class="stat-value">{{ $maintenanceRequests->where('status', 'open')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">In Progress</p>
        <p class="stat-value">{{ $maintenanceRequests->where('status', 'in_progress')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Resolved</p>
        <p class="stat-value">{{ $maintenanceRequests->where('status', 'resolved')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Maintenance Issues</p>

        <span class="page-card-note">
            <i class="fas fa-tools"></i>
            Track issue category, priority, status and assigned person
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Maintenance">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Branch</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assigned</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($maintenanceRequests as $item)
                    <tr>
                        <td>
                            <p class="table-main-text">{{ $item->title }}</p>
                            <p class="table-sub-text">
                                {{ $item->description ? \Illuminate\Support\Str::limit($item->description, 55) : 'Maintenance issue' }}
                            </p>
                        </td>

                        <td>
                            <p class="table-main-text">{{ $item->branch->name ?? '-' }}</p>
                            <p class="table-sub-text">Branch</p>
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ ucfirst(str_replace('_', ' ', $item->category ?? '-')) }}
                            </span>
                        </td>

                        <td>
                            @if($item->priority === 'high' || $item->priority === 'urgent')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">
                                    {{ ucfirst($item->priority) }}
                                </span>
                            @elseif($item->priority === 'medium')
                                <span class="status-pill warning">Medium</span>
                            @elseif($item->priority === 'low')
                                <span class="status-pill success">Low</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($item->priority ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($item->status === 'resolved' || $item->status === 'completed' || $item->status === 'closed')
                                <span class="status-pill success">
                                    {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                </span>
                            @elseif($item->status === 'in_progress')
                                <span class="status-pill warning">In Progress</span>
                            @elseif($item->status === 'open' || $item->status === 'pending')
                                <span class="status-pill" style="background:#DBEAFE;color:#1D4ED8;">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @elseif($item->status === 'rejected')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Rejected</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst(str_replace('_', ' ', $item->status ?? '-')) }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $assignedName = $item->assignedTo->name ?? 'Not Assigned';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($assignedName, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $assignedName }}</p>
                                    <p class="table-sub-text">Assigned To</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="action-row">
                                @can('maintenance_show')
                                    <a class="btn-outline" href="{{ route('admin.maintenance-requests.show', $item->id) }}">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('maintenance_edit')
                                    <a class="btn-outline btn-outline-edit" href="{{ route('admin.maintenance-requests.edit', $item->id) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('maintenance_delete')
                                    <form action="{{ route('admin.maintenance-requests.destroy', $item->id) }}"
                                          method="POST"
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

@endsection

@section('scripts')
@parent
<script>
$(function () {
    initAdminDataTable('.datatable-Maintenance', {
        searchPlaceholder: 'Search maintenance...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ maintenance issues'
    });
});
</script>
@endsection