@extends('layouts.admin')

@section('page-title', 'Exam Types')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Exam Types</h2>
        <p class="admin-page-subtitle">
            Manage exam/test types used while creating an exam
        </p>
    </div>

    @can('exam_type_create')
        <a href="{{ route('admin.exam-types.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Exam Type
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Exam Types</p>
        <p class="stat-value">{{ $examTypes->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $examTypes->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Inactive</p>
        <p class="stat-value">{{ $examTypes->where('status', 'inactive')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Exam Types</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Select rows to use bulk actions
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-ExamType">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Exam Type</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($examTypes as $examType)
                    <tr data-entry-id="{{ $examType->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color  = $colors[$examType->id % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($examType->name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $examType->name }}</p>
                                    <p class="table-sub-text">
                                        {{ $examType->description ? \Illuminate\Support\Str::limit($examType->description, 35) : 'Exam Type' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($examType->status == 'active')
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
                                @can('exam_type_show')
                                    <a href="{{ route('admin.exam-types.show', $examType->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('exam_type_edit')
                                    <a href="{{ route('admin.exam-types.edit', $examType->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('exam_type_delete')
                                    <form action="{{ route('admin.exam-types.destroy', $examType->id) }}"
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
    initAdminDataTable('.datatable-ExamType', {
        canDelete: @can('exam_type_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.exam-types.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search exam types...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ exam types'
    });
});
</script>
@endsection
