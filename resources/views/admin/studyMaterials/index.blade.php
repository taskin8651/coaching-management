@extends('layouts.admin')

@section('page-title', 'Study Materials')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Study Materials</h2>
        <p class="admin-page-subtitle">
            Manage notes, PDFs, assignments, practice papers and video links
        </p>
    </div>

    @can('study_material_create')
        <a href="{{ route('admin.study-materials.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Material
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Materials</p>
        <p class="stat-value">{{ $studyMaterials->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $studyMaterials->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Inactive</p>
        <p class="stat-value">{{ $studyMaterials->where('status', 'inactive')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">With Files</p>
        <p class="stat-value">{{ $studyMaterials->filter(fn($m) => count($m->files))->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Study Materials</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Students can view/download active material
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-StudyMaterial">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Material</th>
                    <th>Type</th>
                    <th>Branch</th>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Subject</th>
                    <th>Files</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($studyMaterials as $material)
                    <tr data-entry-id="{{ $material->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $material->id }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$material->id % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    <i class="fas fa-book-reader"></i>
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $material->title }}</p>
                                    <p class="table-sub-text">
                                        Uploaded by: {{ $material->uploadedBy->name ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="role-tag">{{ $material->material_type ?? '-' }}</span>
                        </td>

                        <td>{{ $material->branch->name ?? '-' }}</td>
                        <td>{{ $material->course->name ?? '-' }}</td>
                        <td>{{ $material->batch->name ?? '-' }}</td>
                        <td>{{ $material->subject->name ?? '-' }}</td>

                        <td>
                            <span class="code-pill">
                                {{ count($material->files) }} File(s)
                            </span>
                        </td>

                        <td>
                            @if($material->status == 'active')
                                <span class="status-pill success">Active</span>
                            @else
                                <span class="status-pill warning">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('study_material_show')
                                    <a href="{{ route('admin.study-materials.show', $material->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('study_material_edit')
                                    <a href="{{ route('admin.study-materials.edit', $material->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('study_material_delete')
                                    <form action="{{ route('admin.study-materials.destroy', $material->id) }}"
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
    initAdminDataTable('.datatable-StudyMaterial', {
        canDelete: @can('study_material_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.study-materials.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search study materials...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ materials'
    });
});
</script>
@endsection