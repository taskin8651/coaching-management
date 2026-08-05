@extends('layouts.admin')

@section('page-title', 'Homework')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Homework</h2>
        <p class="admin-page-subtitle">
            Assignments, due dates and completion tracking
        </p>
    </div>

    @can('homework_create')
        <a href="{{ route('admin.homeworks.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Homework
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Homework</p>
        <p class="stat-value">{{ $homeworks->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $homeworks->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Pending</p>
        <p class="stat-value">{{ $homeworks->where('status', 'pending')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Completed</p>
        <p class="stat-value">{{ $homeworks->where('status', 'completed')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Pending Approval</p>
        <p class="stat-value">{{ $homeworks->where('approval_status', 'pending')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Homework</p>

        <span class="page-card-note">
            <i class="fas fa-book-open"></i>
            Track homework assigned by teachers
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Homeworks">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Batch</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Approval</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($homeworks as $item)
                    <tr>
                        <td>
                            <p class="table-main-text">{{ $item->title }}</p>
                            <p class="table-sub-text">
                                {{ $item->description ? \Illuminate\Support\Str::limit($item->description, 55) : 'Homework assignment' }}
                            </p>
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ $item->batch->name ?? '-' }}
                            </span>
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
                            {{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('d M Y') : '-' }}
                        </td>

                        <td>
                            @if($item->status == 'active')
                                <span class="status-pill success">Active</span>
                            @elseif($item->status == 'completed')
                                <span class="status-pill success">Completed</span>
                            @elseif($item->status == 'pending')
                                <span class="status-pill warning">Pending</span>
                            @elseif($item->status == 'inactive')
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">Inactive</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($item->status ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($item->approval_status == 'approved')
                                <span class="status-pill success">Approved</span>
                            @elseif($item->approval_status == 'rejected')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Rejected</span>
                            @else
                                <span class="status-pill warning">Pending</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('homework_show')
                                    <a class="btn-outline" href="{{ route('admin.homeworks.show', $item->id) }}">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @else
                                    <a class="btn-outline" href="{{ route('admin.homeworks.show', $item->id) }}">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('homework_edit')
                                    <a class="btn-outline btn-outline-edit" href="{{ route('admin.homeworks.edit', $item->id) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('homework_approve')
                                    @if($item->approval_status !== 'approved')
                                        <form action="{{ route('admin.homeworks.approve', $item->id) }}"
                                              method="POST"
                                              style="display:inline;">
                                            @csrf

                                            <button type="submit" class="btn-outline">
                                                <i class="fas fa-check"></i>
                                                Approve
                                            </button>
                                        </form>
                                    @endif
                                @endcan

                                @can('homework_delete')
                                    <form action="{{ route('admin.homeworks.destroy', $item->id) }}"
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
    initAdminDataTable('.datatable-Homeworks', {
        canDelete: @can('homework_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.homeworks.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search homework...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ homework records'
    });
});
</script>
@endsection