@extends('layouts.admin')

@section('page-title', 'Students')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Students</h2>
        <p class="admin-page-subtitle">
            Manage student admissions, branch, course, batch and documents
        </p>
    </div>

    @can('student_create')
        <a href="{{ route('admin.students.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Student
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Students</p>
        <p class="stat-value">{{ $students->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $students->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Completed</p>
        <p class="stat-value">{{ $students->where('status', 'completed')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Dropped</p>
        <p class="stat-value">{{ $students->where('status', 'dropped')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Students</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Select rows to use bulk actions
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Student">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Code</th>
                    <th>Branch</th>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($students as $student)
                    <tr data-entry-id="{{ $student->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @if($student->photo)
                                    <img src="{{ $student->photo }}"
                                         alt="{{ $student->user->name ?? 'Student' }}"
                                         class="avatar-circle"
                                         style="object-fit:cover;">
                                @else
                                    @php
                                        $name = $student->user->name ?? 'S';
                                        $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                        $color  = $colors[$student->id % count($colors)];
                                    @endphp

                                    <div class="avatar-circle" style="background: {{ $color }};">
                                        {{ strtoupper(substr($name, 0, 1)) }}
                                    </div>
                                @endif

                                <div>
                                    <p class="table-main-text">{{ $student->user->name ?? '-' }}</p>
                                    <p class="table-sub-text">
                                        {{ $student->user->email ?? 'Student Profile' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($student->student_code)
                                <span class="code-pill">{{ $student->student_code }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">—</span>
                            @endif
                        </td>

                        <td>
                            @if($student->branch)
                                <span class="role-tag">{{ $student->branch->name }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">No Branch</span>
                            @endif
                        </td>

                        <td>
                            @if($student->course)
                                <span class="role-tag">{{ $student->course->name }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">No Course</span>
                            @endif
                        </td>

                        <td>
                            @if($student->batch)
                                <span class="role-tag">{{ $student->batch->name }}</span>
                            @else
                                <span style="font-size:12px; color:#94A3B8;">No Batch</span>
                            @endif
                        </td>

                        <td style="color:#475569;">
                            {{ $student->phone ?? '-' }}
                        </td>

                        <td>
                            @if($student->status == 'active')
                                <div class="d-flex align-items-center gap-2">
                                    <span class="status-dot status-success"></span>
                                    <span style="font-size:12.5px; color:#166534;">Active</span>
                                </div>
                            @elseif($student->status == 'completed')
                                <div class="d-flex align-items-center gap-2">
                                    <span class="status-dot status-info"></span>
                                    <span style="font-size:12.5px; color:#075985;">Completed</span>
                                </div>
                            @elseif($student->status == 'dropped')
                                <div class="d-flex align-items-center gap-2">
                                    <span class="status-dot status-danger"></span>
                                    <span style="font-size:12.5px; color:#991B1B;">Dropped</span>
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
                                @can('student_show')
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('student_edit')
                                    <a href="{{ route('admin.students.edit', $student->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('student_delete')
                                    <form action="{{ route('admin.students.destroy', $student->id) }}"
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
    initAdminDataTable('.datatable-Student', {
        canDelete: @can('student_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.students.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search students...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ students'
    });
});
</script>
@endsection