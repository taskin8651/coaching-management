@extends('layouts.admin')

@section('page-title', 'Notices')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Notices / Announcements</h2>
        <p class="admin-page-subtitle">
            Manage notices for students, teachers, staff, branches, courses and batches
        </p>
    </div>

    @can('notice_create')
        <a href="{{ route('admin.notices.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Notice
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Notices</p>
        <p class="stat-value">{{ $notices->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Published</p>
        <p class="stat-value">{{ $notices->where('status', 'published')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Draft</p>
        <p class="stat-value">{{ $notices->where('status', 'draft')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Inactive</p>
        <p class="stat-value">{{ $notices->where('status', 'inactive')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Notices</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Publish notice according to target audience
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Notice">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Notice</th>
                    <th>Type</th>
                    <th>Audience</th>
                    <th>Branch</th>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Publish</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($notices as $notice)
                    <tr data-entry-id="{{ $notice->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$notice->id % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    <i class="fas fa-bullhorn"></i>
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $notice->title }}</p>
                                    <p class="table-sub-text">
                                        By: {{ $notice->createdBy->name ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="role-tag">{{ $notice->notice_type ?? '-' }}</span>
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ ucwords(str_replace('_', ' ', $notice->target_audience)) }}
                            </span>
                        </td>

                        <td>{{ $notice->branch->name ?? '-' }}</td>
                        <td>{{ $notice->course->name ?? '-' }}</td>
                        <td>{{ $notice->batch->name ?? '-' }}</td>

                        <td>
                            {{ $notice->publish_date ? \Carbon\Carbon::parse($notice->publish_date)->format('d M Y') : '-' }}
                        </td>

                        <td>
                            @if($notice->status == 'published')
                                <span class="status-pill success">Published</span>
                            @elseif($notice->status == 'draft')
                                <span class="status-pill warning">Draft</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('notice_show')
                                    <a href="{{ route('admin.notices.show', $notice->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('notice_edit')
                                    <a href="{{ route('admin.notices.edit', $notice->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('notice_delete')
                                    <form action="{{ route('admin.notices.destroy', $notice->id) }}"
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
    initAdminDataTable('.datatable-Notice', {
        canDelete: @can('notice_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.notices.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search notices...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ notices'
    });
});
</script>
@endsection