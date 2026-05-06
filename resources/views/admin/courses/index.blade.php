@extends('layouts.admin')

@section('page-title', 'Courses')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Courses</h2>
        <p class="admin-page-subtitle">
            Manage branch-wise coaching courses, fees and status
        </p>
    </div>

    @can('course_create')
        <a href="{{ route('admin.courses.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Course
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Courses</p>
        <p class="stat-value">{{ $courses->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $courses->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Inactive</p>
        <p class="stat-value">{{ $courses->where('status', 'inactive')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Total Fees</p>
        <p class="stat-value">₹{{ number_format($courses->sum('fee'), 0) }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Courses</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Select rows to use bulk actions
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Course">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Course</th>
                    <th>Code</th>
                    <th>Branch</th>
                    <th>Duration</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($courses as $course)
                    <tr data-entry-id="{{ $course->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $course->id }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @if($course->image)
                                    <img src="{{ $course->image }}"
                                         alt="{{ $course->name }}"
                                         class="avatar-circle"
                                         style="object-fit:cover;">
                                @else
                                    @php
                                        $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                        $color  = $colors[$course->id % count($colors)];
                                    @endphp

                                    <div class="avatar-circle" style="background: {{ $color }};">
                                        {{ strtoupper(substr($course->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div>
                                    <p class="table-main-text">{{ $course->name }}</p>
                                    <p class="table-sub-text">
                                        {{ $course->description ? \Illuminate\Support\Str::limit($course->description, 35) : 'Coaching Course' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($course->course_code)
                                <span class="code-pill">{{ $course->course_code }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            @if($course->branch)
                                <span class="role-tag">{{ $course->branch->name }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">No Branch</span>
                            @endif
                        </td>

                        <td style="color:#475569;">
                            {{ $course->duration ?? '-' }}
                        </td>

                        <td>
                            <strong>₹{{ number_format($course->fee, 2) }}</strong>
                        </td>

                        <td>
                            @if($course->status == 'active')
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
                                @can('course_show')
                                    <a href="{{ route('admin.courses.show', $course->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('course_edit')
                                    <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('course_delete')
                                    <form action="{{ route('admin.courses.destroy', $course->id) }}"
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
    initAdminDataTable('.datatable-Course', {
        canDelete: @can('course_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.courses.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search courses...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ courses'
    });
});
</script>
@endsection