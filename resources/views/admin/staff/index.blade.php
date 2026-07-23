@extends('layouts.admin')

@section('page-title', 'Staff')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Staff</h2>
        <p class="admin-page-subtitle">
            Manage branch-wise staff profiles, designation, salary and documents
        </p>
    </div>

    @can('staff_create')
        <a href="{{ route('admin.staff.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Staff
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Staff</p>
        <p class="stat-value">{{ $staff->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $staff->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Inactive</p>
        <p class="stat-value">{{ $staff->where('status', 'inactive')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Total Salary</p>
        <p class="stat-value">₹{{ number_format($staff->sum('salary'), 0) }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Staff</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Select rows to use bulk actions
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Staff">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Staff</th>
                    <th>Branch</th>
                    <th>Phone</th>
                    <th>Designation</th>
                    <th>Department</th>
                    <th>Salary</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($staff as $member)
                    <tr data-entry-id="{{ $member->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @if($member->photo)
                                    <img src="{{ $member->photo }}"
                                         alt="{{ $member->user->name ?? 'Staff' }}"
                                         class="avatar-circle"
                                         style="object-fit:cover;">
                                @else
                                    @php
                                        $name = $member->user->name ?? 'S';
                                        $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                        $color  = $colors[$member->id % count($colors)];
                                    @endphp

                                    <div class="avatar-circle" style="background: {{ $color }};">
                                        {{ strtoupper(substr($name, 0, 1)) }}
                                    </div>
                                @endif

                                <div>
                                    <p class="table-main-text">{{ $member->user->name ?? '-' }}</p>
                                    <p class="table-sub-text">
                                        {{ $member->user->email ?? 'Staff Profile' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($member->branch)
                                <span class="role-tag">{{ $member->branch->name }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">No Branch</span>
                            @endif
                        </td>

                        <td style="color:#475569;">
                            {{ $member->phone ?? '-' }}
                        </td>

                        <td>
                            {{ $member->designation ?? '-' }}
                        </td>

                        <td>
                            {{ $member->department ?? '-' }}
                        </td>

                        <td>
                            <strong>₹{{ number_format($member->salary, 2) }}</strong>
                        </td>

                        <td>
                            @if($member->status == 'active')
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
                                @can('staff_show')
                                    <a href="{{ route('admin.staff.show', $member->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('staff_edit')
                                    <a href="{{ route('admin.staff.edit', $member->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('staff_delete')
                                    <form action="{{ route('admin.staff.destroy', $member->id) }}"
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
    initAdminDataTable('.datatable-Staff', {
        canDelete: @can('staff_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.staff.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search staff...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ staff'
    });
});
</script>
@endsection