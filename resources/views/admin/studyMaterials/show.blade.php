@extends('layouts.admin')

@section('page-title', 'Show Study Material')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.study-materials.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">{{ $studyMaterial->title }}</h2>

        <p class="admin-page-subtitle">
            Study material details, files and external links
        </p>
    </div>

    <div class="show-actions">
        @can('study_material_edit')
            <a href="{{ route('admin.study-materials.edit', $studyMaterial->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Material
            </a>
        @endcan

        @can('study_material_delete')
            <form action="{{ route('admin.study-materials.destroy', $studyMaterial->id) }}"
                  method="POST"
                  onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                @method('DELETE')
                @csrf

                <button type="submit" class="btn-danger">
                    <i class="fas fa-trash-alt"></i>
                    Delete
                </button>
            </form>
        @endcan
    </div>
</div>

<div class="show-grid">

    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background:#4F46E5;">
                    <i class="fas fa-book-reader"></i>
                </div>

                <p class="profile-title">{{ $studyMaterial->title }}</p>

                <p class="profile-subtitle">
                    {{ $studyMaterial->material_type ?? 'Study Material' }}
                </p>

                @if($studyMaterial->status == 'active')
                    <span class="status-pill success">Active</span>
                @else
                    <span class="status-pill warning">Inactive</span>
                @endif
            </div>

            <div class="detail-section-pad-sm">
                <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <div class="stat-mini">
                        <p class="stat-mini-label">Material ID</p>
                        <p class="stat-mini-value">#{{ $studyMaterial->id }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Files</p>
                        <p class="stat-mini-value">{{ count($studyMaterial->files) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Type</p>
                        <p class="stat-mini-value-sm">{{ $studyMaterial->material_type ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Uploaded By</p>
                        <p class="stat-mini-value-sm">{{ $studyMaterial->uploadedBy->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('study_material_edit')
                    <a href="{{ route('admin.study-materials.edit', $studyMaterial->id) }}" class="quick-link primary">
                        <i class="fas fa-pencil-alt"></i>
                        Edit Material
                    </a>
                @endcan

                <a href="{{ route('admin.study-materials.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Materials
                </a>

                @can('study_material_create')
                    <a href="{{ route('admin.study-materials.create') }}" class="quick-link">
                        <i class="fas fa-plus"></i>
                        Add New Material
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-info-circle"></i>
                </div>

                <p class="detail-section-title">Material Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Title</span>
                    <span class="detail-value">{{ $studyMaterial->title }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Material Type</span>
                    <span class="detail-value">{{ $studyMaterial->material_type ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">{{ $studyMaterial->branch->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Course</span>
                    <span class="detail-value">{{ $studyMaterial->course->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Batch</span>
                    <span class="detail-value">{{ $studyMaterial->batch->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Subject</span>
                    <span class="detail-value">{{ $studyMaterial->subject->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Uploaded By</span>
                    <span class="detail-value">{{ $studyMaterial->uploadedBy->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Description</span>
                    <span class="detail-value">{{ $studyMaterial->description ?? '-' }}</span>
                </div>
            </div>
        </div>

        @if($studyMaterial->external_link)
            <div class="detail-card mb-3">
                <div class="detail-section-head">
                    <div class="detail-section-icon">
                        <i class="fas fa-link"></i>
                    </div>

                    <p class="detail-section-title">External Link</p>
                </div>

                <div class="detail-section-body">
                    <div class="detail-row">
                        <span class="detail-label">Link</span>
                        <span class="detail-value">
                            <a href="{{ $studyMaterial->external_link }}" target="_blank">
                                {{ $studyMaterial->external_link }}
                            </a>
                        </span>
                    </div>
                </div>
            </div>
        @endif

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-file"></i>
                </div>

                <p class="detail-section-title">Uploaded Files</p>
            </div>

            <div class="detail-section-body">
                @if($studyMaterial->files && count($studyMaterial->files))
                    @foreach($studyMaterial->files as $file)
                        <div class="detail-row">
                            <span class="detail-label">File</span>
                            <span class="detail-value">
                                <a href="{{ $file['url'] }}" target="_blank">
                                    <i class="fas fa-download"></i>
                                    {{ $file['name'] }}
                                </a>
                            </span>
                        </div>
                    @endforeach
                @else
                    <div class="detail-row">
                        <span class="detail-label">Files</span>
                        <span class="detail-value">No files uploaded.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection