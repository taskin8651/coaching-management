@extends('layouts.admin')

@section('page-title', 'Show Student')

@section('content')

@php
    $name = $student->user->name ?? 'Student';
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
    $color = $colors[$student->id % count($colors)];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.students.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Student Details</h2>

        <p class="admin-page-subtitle">
            Full details for this coaching student
        </p>
    </div>

    <div class="show-actions">
        @can('student_edit')
            <a href="{{ route('admin.students.edit', $student->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Student
            </a>
        @endcan

        @can('student_delete')
            <form action="{{ route('admin.students.destroy', $student->id) }}"
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
                @if($student->photo)
                    <img src="{{ $student->photo }}"
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
                    {{ $student->student_code ? 'Code: ' . $student->student_code : 'Student Profile' }}
                </p>

                @if($student->status == 'active')
                    <span class="status-pill success">
                        <i class="fas fa-check-circle"></i>
                        Active
                    </span>
                @elseif($student->status == 'completed')
                    <span class="status-pill" style="background:#E0F2FE;color:#075985;">
                        <i class="fas fa-check-double"></i>
                        Completed
                    </span>
                @elseif($student->status == 'dropped')
                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">
                        <i class="fas fa-times-circle"></i>
                        Dropped
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
                        <p class="stat-mini-label">Student ID</p>
                        <p class="stat-mini-value">#{{ $student->id }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Code</p>
                        <p class="stat-mini-value-sm">{{ $student->student_code ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Branch</p>
                        <p class="stat-mini-value-sm">{{ $student->branch->name ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Admission</p>
                        <p class="stat-mini-value-sm">
                            {{ optional($student->admission_date)->format('d M Y') ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('student_edit')
                    <a href="{{ route('admin.students.edit', $student->id) }}" class="quick-link primary">
                        <i class="fas fa-user-graduate"></i>
                        Edit Student
                    </a>
                @endcan

                <a href="{{ route('admin.students.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Students
                </a>

                @can('student_create')
                    <a href="{{ route('admin.students.create') }}" class="quick-link">
                        <i class="fas fa-plus"></i>
                        Add New Student
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <p class="detail-section-title">Student Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">ID</span>
                    <span class="detail-value code-pill">#{{ $student->id }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">{{ $student->user->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ $student->user->email ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Student Code</span>
                    <span class="detail-value">{{ $student->student_code ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Notification Number</span>
                    <span class="detail-value">{{ $student->notification_phone ?: $student->phone ?: '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Student Personal Number</span>
                    <span class="detail-value">{{ $student->student_personal_phone ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Gender</span>
                    <span class="detail-value">{{ $student->gender ? ucfirst($student->gender) : '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Date of Birth</span>
                    <span class="detail-value">
                        {{ optional($student->date_of_birth)->format('d M Y') ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">{{ ucfirst($student->status) }}</span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-layer-group"></i>
                </div>

                <p class="detail-section-title">Academic Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">{{ $student->branch->name ?? 'No Branch' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Course</span>
                    <span class="detail-value">{{ $student->course->name ?? 'No Course' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Batch</span>
                    <span class="detail-value">{{ $student->batch->name ?? 'No Batch' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">School / College</span>
                    <span class="detail-value">{{ $student->school_name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Class</span>
                    <span class="detail-value">{{ $student->class_name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Admission Date</span>
                    <span class="detail-value">
                        {{ optional($student->admission_date)->format('d M Y') ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-users"></i>
                </div>

                <p class="detail-section-title">Guardian Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Father Name</span>
                    <span class="detail-value">{{ $student->father_name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Father's Number</span>
                    <span class="detail-value">{{ $student->father_phone ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Mother Name</span>
                    <span class="detail-value">{{ $student->mother_name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Mother's Number</span>
                    <span class="detail-value">{{ $student->mother_phone ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Guardian Number</span>
                    <span class="detail-value">{{ $student->guardian_phone ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Address</span>
                    <span class="detail-value">{{ $student->address ?? '-' }}</span>
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
                @if($student->documents && count($student->documents))
                    @foreach($student->documents as $document)
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
