@extends('layouts.admin')

@section('page-title', 'Branches')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Branches</h2>
        <p class="admin-page-subtitle">
            Manage all coaching branches, managers and branch status
        </p>
    </div>

    @can('branch_create')
        <a href="{{ route('admin.branches.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Branch
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Branches</p>
        <p class="stat-value">{{ $branches->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $branches->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Inactive</p>
        <p class="stat-value">{{ $branches->where('status', 'inactive')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">With Manager</p>
        <p class="stat-value">{{ $branches->whereNotNull('manager_id')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Branches</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Select rows to use bulk actions
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Branch">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Branch</th>
                    <th>Code</th>
                    <th>Manager</th>
                    <th>Contact</th>
                    <th>City</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($branches as $branch)
                    <tr data-entry-id="{{ $branch->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @if($branch->logo)
                                    <img src="{{ $branch->logo }}"
                                         alt="{{ $branch->name }}"
                                         class="avatar-circle"
                                         style="object-fit:cover;">
                                @else
                                    @php
                                        $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                        $color  = $colors[$branch->id % count($colors)];
                                    @endphp

                                    <div class="avatar-circle" style="background: {{ $color }};">
                                        {{ strtoupper(substr($branch->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div>
                                    <p class="table-main-text">{{ $branch->name }}</p>
                                    <p class="table-sub-text">
                                        {{ $branch->address ? Str::limit($branch->address, 35) : 'Coaching Branch' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($branch->branch_code)
                                <span class="code-pill">{{ $branch->branch_code }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            @if($branch->manager)
                                <span class="role-tag">{{ $branch->manager->name }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">Not Assigned</span>
                            @endif
                        </td>

                        <td style="color:#475569;">
                            <div>
                                <p class="table-main-text" style="font-size:13px;">
                                    {{ $branch->phone ?? '-' }}
                                </p>
                                <p class="table-sub-text">
                                    {{ $branch->email ?? '-' }}
                                </p>
                            </div>
                        </td>

                        <td style="color:#475569;">
                            {{ $branch->city ?? '-' }}
                        </td>

                        <td>
                            @if($branch->status == 'active')
                                <div class="d-flex align-items-center gap-2">
                                    <span class="status-dot status-success"></span>
                                    <span style="font-size:12.5px; color:#166534;">Active</span>
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
                                @can('branch_show')
                                    <a href="{{ route('admin.branches.show', $branch->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('branch_edit')
                                    <a href="{{ route('admin.branches.edit', $branch->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('branch_delete')
                                    <form action="{{ route('admin.branches.destroy', $branch->id) }}"
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
    initAdminDataTable('.datatable-Branch', {
        canDelete: @can('branch_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.branches.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search branches...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ branches'
    });
});
</script>
@endsection