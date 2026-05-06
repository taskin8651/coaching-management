@extends('layouts.admin')

@section('page-title', 'Subjects')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Subjects</h2>
        <p class="admin-page-subtitle">
            Manage course-wise subjects for coaching branches
        </p>
    </div>

    @can('subject_create')
        <a href="{{ route('admin.subjects.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Subject
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Subjects</p>
        <p class="stat-value">{{ $subjects->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $subjects->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Inactive</p>
        <p class="stat-value">{{ $subjects->where('status', 'inactive')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Courses Linked</p>
        <p class="stat-value">{{ $subjects->whereNotNull('course_id')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Subjects</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Select rows to use bulk actions
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Subject">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>Code</th>
                    <th>Branch</th>
                    <th>Course</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($subjects as $subject)
                    <tr data-entry-id="{{ $subject->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $subject->id }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color  = $colors[$subject->id % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($subject->name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $subject->name }}</p>
                                    <p class="table-sub-text">
                                        {{ $subject->description ? \Illuminate\Support\Str::limit($subject->description, 35) : 'Coaching Subject' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($subject->subject_code)
                                <span class="code-pill">{{ $subject->subject_code }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            @if($subject->branch)
                                <span class="role-tag">{{ $subject->branch->name }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">No Branch</span>
                            @endif
                        </td>

                        <td>
                            @if($subject->course)
                                <span class="role-tag">{{ $subject->course->name }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">No Course</span>
                            @endif
                        </td>

                        <td>
                            @if($subject->status == 'active')
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
                                @can('subject_show')
                                    <a href="{{ route('admin.subjects.show', $subject->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('subject_edit')
                                    <a href="{{ route('admin.subjects.edit', $subject->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('subject_delete')
                                    <form action="{{ route('admin.subjects.destroy', $subject->id) }}"
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
    initAdminDataTable('.datatable-Subject', {
        canDelete: @can('subject_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.subjects.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search subjects...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ subjects'
    });
});
</script>
@endsection