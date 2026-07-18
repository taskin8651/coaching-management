@extends('layouts.admin')

@section('page-title', 'Teachers')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Teachers</h2>
        <p class="admin-page-subtitle">
            Manage teacher profile, branch, course, subject and batch assignment
        </p>
    </div>

    @can('teacher_create')
        <a href="{{ route('admin.teachers.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Teacher
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Teachers</p>
        <p class="stat-value">{{ $teachers->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $teachers->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Inactive</p>
        <p class="stat-value">{{ $teachers->where('status', 'inactive')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Assignments</p>
        <p class="stat-value">{{ $teachers->sum(fn($teacher) => $teacher->assignments->count()) }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Teachers</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Teachers are filtered by assigned branch
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Teacher">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Teacher</th>
                    <th>Branch</th>
                    <th>Phone</th>
                    <th>Specialization</th>
                    <th>Assignments</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($teachers as $teacher)
                    <tr data-entry-id="{{ $teacher->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $name = $teacher->user->name ?? 'Teacher';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$teacher->id % count($colors)];
                                @endphp

                                @if($teacher->photo)
                                    <img src="{{ $teacher->photo }}" class="avatar-circle" style="object-fit:cover;">
                                @else
                                    <div class="avatar-circle" style="background: {{ $color }};">
                                        {{ strtoupper(substr($name, 0, 1)) }}
                                    </div>
                                @endif

                                <div>
                                    <p class="table-main-text">{{ $name }}</p>
                                    <p class="table-sub-text">{{ $teacher->qualification ?? '-' }}</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            {{ $teacher->branch->name ?? '-' }}
                        </td>

                        <td>
                            {{ $teacher->phone ?? '-' }}
                        </td>

                        <td>
                            {{ $teacher->subject_specialization ?? '-' }}
                        </td>

                        <td>
                            @forelse($teacher->assignments as $assignment)
                                <div class="assignment-mini">
                                    <strong>{{ $assignment->course->name ?? '-' }}</strong>
                                    <span>
                                        {{ $assignment->subject->name ?? '-' }}
                                        /
                                        {{ $assignment->batch->name ?? '-' }}
                                    </span>
                                </div>
                            @empty
                                <span class="table-sub-text">No assignment</span>
                            @endforelse
                        </td>

                        <td>
                            @if($teacher->status == 'active')
                                <span class="status-pill success">Active</span>
                            @else
                                <span class="status-pill warning">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('teacher_show')
                                    <a href="{{ route('admin.teachers.show', $teacher->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('teacher_edit')
                                    <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('teacher_delete')
                                    <form action="{{ route('admin.teachers.destroy', $teacher->id) }}"
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

<style>
.assignment-mini {
    padding: 6px 9px;
    border-radius: 10px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    margin-bottom: 5px;
    max-width: 260px;
}
.assignment-mini strong {
    display: block;
    font-size: 12px;
    color: #0F172A;
}
.assignment-mini span {
    display: block;
    font-size: 11px;
    color: #64748B;
    margin-top: 2px;
}
</style>

@endsection

@section('scripts')
@parent
<script>
$(function () {
    initAdminDataTable('.datatable-Teacher', {
        canDelete: @can('teacher_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.teachers.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search teachers...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ teachers'
    });
});
</script>
@endsection