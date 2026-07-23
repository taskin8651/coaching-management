@extends('layouts.admin')

@section('page-title', 'Batches')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Batches</h2>
        <p class="admin-page-subtitle">
            Manage course-wise batches, timings and student capacity
        </p>
    </div>

    @can('batch_create')
        <a href="{{ route('admin.batches.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Batch
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Batches</p>
        <p class="stat-value">{{ $batches->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $batches->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Completed</p>
        <p class="stat-value">{{ $batches->where('status', 'completed')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Total Capacity</p>
        <p class="stat-value">{{ $batches->sum('max_students') }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Batches</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Select rows to use bulk actions
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Batch">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Batch</th>
                    <th>Code</th>
                    <th>Branch</th>
                    <th>Course</th>
                    <th>Timing</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($batches as $batch)
                    <tr data-entry-id="{{ $batch->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color  = $colors[$batch->id % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($batch->name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $batch->name }}</p>
                                    <p class="table-sub-text">
                                        {{ $batch->description ? \Illuminate\Support\Str::limit($batch->description, 35) : 'Coaching Batch' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($batch->batch_code)
                                <span class="code-pill">{{ $batch->batch_code }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            @if($batch->branch)
                                <span class="role-tag">{{ $batch->branch->name }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">No Branch</span>
                            @endif
                        </td>

                        <td>
                            @if($batch->course)
                                <span class="role-tag">{{ $batch->course->name }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">No Course</span>
                            @endif
                        </td>

                        <td style="color:#475569;">
                            @if($batch->start_time || $batch->end_time)
                                {{ $batch->start_time ? \Carbon\Carbon::parse($batch->start_time)->format('h:i A') : '-' }}
                                -
                                {{ $batch->end_time ? \Carbon\Carbon::parse($batch->end_time)->format('h:i A') : '-' }}
                            @else
                                -
                            @endif
                        </td>

                        <td>
                            {{ $batch->max_students ?? '-' }}
                        </td>

                        <td>
                            @if($batch->status == 'active')
                                <div class="d-flex align-items-center gap-2">
                                    <span class="status-dot status-success"></span>
                                    <span style="font-size:12.5px; color:#166534;">Active</span>
                                </div>
                            @elseif($batch->status == 'completed')
                                <div class="d-flex align-items-center gap-2">
                                    <span class="status-dot status-info"></span>
                                    <span style="font-size:12.5px; color:#075985;">Completed</span>
                                </div>
                            @else
                                <div class="d-flex align-items-center gap-2">
                                    <span class="status-dot status-warning"></span>
                                    <span style="font-size:12.5px; color:#92400E;">Inactive</span>
                                </div>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('batch_show')
                                    <a href="{{ route('admin.batches.show', $batch->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('batch_edit')
                                    <a href="{{ route('admin.batches.edit', $batch->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('batch_delete')
                                    <form action="{{ route('admin.batches.destroy', $batch->id) }}"
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
    initAdminDataTable('.datatable-Batch', {
        canDelete: @can('batch_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.batches.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search batches...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ batches'
    });
});
</script>
@endsection