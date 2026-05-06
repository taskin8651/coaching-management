@extends('layouts.admin')

@section('page-title', 'Show Teacher')

@section('content')

@php
    $name = $teacher->user->name ?? 'Teacher';
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
    $color = $colors[$teacher->id % count($colors)];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.teachers.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Teacher Details</h2>

        <p class="admin-page-subtitle">
            Full details for this coaching teacher
        </p>
    </div>

    <div class="show-actions">
        @can('teacher_edit')
            <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Teacher
            </a>
        @endcan

        @can('teacher_delete')
            <form action="{{ route('admin.teachers.destroy', $teacher->id) }}"
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
                @if($teacher->photo)
                    <img src="{{ $teacher->photo }}"
                         alt="{{ $name }}"
                         class="profile-avatar-lg"
                         style="object-fit:cover;">
                @else
                    <div class="profile-avatar-lg" style="background: {{ $color }};">
                        {{ strtoupper(substr($name, 0, 1)) }}
                    </div>
                @endif

                <p class="profile-title">{{ $name }}</p>

                <p class="profile-subtitle">
                    {{ $teacher->subject_specialization ?? 'Coaching Teacher' }}
                </p>

                @if($teacher->status == 'active')
                    <span class="status-pill success">
                        <i class="fas fa-check-circle"></i>
                        Active
                    </span>
                @else
                    <span class="status-pill warning">
                        <i class="fas fa-clock"></i>
                        Inactive
                    </span>
                @endif
            </div>

            <div class="detail-section-pad-sm">
                <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <div class="stat-mini">
                        <p class="stat-mini-label">Teacher ID</p>
                        <p class="stat-mini-value">#{{ $teacher->id }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Salary</p>
                        <p class="stat-mini-value">₹{{ number_format($teacher->salary, 0) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Branch</p>
                        <p class="stat-mini-value-sm">{{ $teacher->branch->name ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Joining</p>
                        <p class="stat-mini-value-sm">
                            {{ optional($teacher->joining_date)->format('d M Y') ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('teacher_edit')
                    <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="quick-link primary">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Edit Teacher
                    </a>
                @endcan

                <a href="{{ route('admin.teachers.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Teachers
                </a>

                @can('teacher_create')
                    <a href="{{ route('admin.teachers.create') }}" class="quick-link">
                        <i class="fas fa-plus"></i>
                        Add New Teacher
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-user"></i>
                </div>

                <p class="detail-section-title">Teacher Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">ID</span>
                    <span class="detail-value code-pill">#{{ $teacher->id }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">{{ $teacher->user->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ $teacher->user->email ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">{{ $teacher->branch->name ?? 'No Branch' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">{{ $teacher->phone ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Alternate Phone</span>
                    <span class="detail-value">{{ $teacher->alternate_phone ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Status</span>

                    @if($teacher->status == 'active')
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-success"></i>
                            <span class="detail-value" style="color:#166534;">Active</span>
                        </div>
                    @else
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-exclamation-circle text-warning"></i>
                            <span class="detail-value" style="color:#92400E;">Inactive</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-briefcase"></i>
                </div>

                <p class="detail-section-title">Professional Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Qualification</span>
                    <span class="detail-value">{{ $teacher->qualification ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Experience</span>
                    <span class="detail-value">{{ $teacher->experience ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Specialization</span>
                    <span class="detail-value">{{ $teacher->subject_specialization ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Salary</span>
                    <span class="detail-value">₹{{ number_format($teacher->salary, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Joining Date</span>
                    <span class="detail-value">
                        {{ optional($teacher->joining_date)->format('d M Y') ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>

                <p class="detail-section-title">Address</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Address</span>
                    <span class="detail-value">{{ $teacher->address ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-file"></i>
                </div>

                <p class="detail-section-title">Documents</p>
            </div>

            <div class="detail-section-body">
                @if($teacher->documents && count($teacher->documents))
                    @foreach($teacher->documents as $document)
                        <div class="detail-row">
                            <span class="detail-label">File</span>
                            <span class="detail-value">
                                <a href="{{ $document['url'] }}" target="_blank">
                                    <i class="fas fa-file"></i>
                                    {{ $document['name'] }}
                                </a>
                            </span>
                        </div>
                    @endforeach
                @else
                    <div class="detail-row">
                        <span class="detail-label">Documents</span>
                        <span class="detail-value">No documents uploaded</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection