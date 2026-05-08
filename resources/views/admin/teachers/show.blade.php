@extends('layouts.admin')

@section('page-title', 'Show Teacher')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.teachers.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">{{ $teacher->user->name ?? 'Teacher' }}</h2>

        <p class="admin-page-subtitle">
            Teacher profile, branch, salary and teaching assignments
        </p>
    </div>

    <div class="show-actions">
        @can('teacher_edit')
            <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Teacher
            </a>
        @endcan
    </div>
</div>

<div class="show-grid">

    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                @if($teacher->photo)
                    <img src="{{ $teacher->photo }}" class="profile-avatar-lg" style="object-fit:cover;">
                @else
                    <div class="profile-avatar-lg" style="background:#F59E0B;">
                        {{ strtoupper(substr($teacher->user->name ?? 'T', 0, 1)) }}
                    </div>
                @endif

                <p class="profile-title">{{ $teacher->user->name ?? '-' }}</p>
                <p class="profile-subtitle">{{ $teacher->qualification ?? 'Teacher' }}</p>

                @if($teacher->status == 'active')
                    <span class="status-pill success">Active</span>
                @else
                    <span class="status-pill warning">Inactive</span>
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
                        <p class="stat-mini-value-sm">₹{{ number_format($teacher->salary ?? 0, 2) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Branch</p>
                        <p class="stat-mini-value-sm">{{ $teacher->branch->name ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Assignments</p>
                        <p class="stat-mini-value">{{ $teacher->assignments->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('teacher_edit')
                    <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="quick-link primary">
                        <i class="fas fa-pencil-alt"></i>
                        Edit Teacher
                    </a>
                @endcan

                <a href="{{ route('admin.teachers.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Teachers
                </a>
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-info-circle"></i>
                </div>

                <p class="detail-section-title">Teacher Information</p>
            </div>

            <div class="detail-section-body">
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
                    <span class="detail-value">{{ $teacher->branch->name ?? '-' }}</span>
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
                    <span class="detail-label">Joining Date</span>
                    <span class="detail-value">
                        {{ $teacher->joining_date ? \Carbon\Carbon::parse($teacher->joining_date)->format('d M Y') : '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Address</span>
                    <span class="detail-value">{{ $teacher->address ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-chalkboard"></i>
                </div>

                <p class="detail-section-title">Teaching Assignments</p>
            </div>

            <div class="detail-section-body">
                @forelse($teacher->assignments as $assignment)
                    <div class="detail-row">
                        <span class="detail-label">Assignment #{{ $loop->iteration }}</span>

                        <span class="detail-value">
                            <strong>{{ $assignment->course->name ?? '-' }}</strong>
                            /
                            {{ $assignment->subject->name ?? '-' }}
                            /
                            {{ $assignment->batch->name ?? '-' }}
                        </span>
                    </div>
                @empty
                    <div class="detail-row">
                        <span class="detail-label">Assignments</span>
                        <span class="detail-value">No teaching assignment found.</span>
                    </div>
                @endforelse
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
                            <span class="detail-label">Document</span>
                            <span class="detail-value">
                                <a href="{{ $document['url'] }}" target="_blank">
                                    <i class="fas fa-download"></i>
                                    {{ $document['name'] }}
                                </a>
                            </span>
                        </div>
                    @endforeach
                @else
                    <div class="detail-row">
                        <span class="detail-label">Documents</span>
                        <span class="detail-value">No documents uploaded.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection