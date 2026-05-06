@extends('layouts.admin')

@section('page-title', 'Show Course')

@section('content')

@php
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
    $color = $colors[$course->id % count($colors)];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.courses.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Course Details</h2>

        <p class="admin-page-subtitle">
            Full details for this coaching course
        </p>
    </div>

    <div class="show-actions">
        @can('course_edit')
            <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Course
            </a>
        @endcan

        @can('course_delete')
            <form action="{{ route('admin.courses.destroy', $course->id) }}"
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
                @if($course->image)
                    <img src="{{ $course->image }}"
                         alt="{{ $course->name }}"
                         class="profile-avatar-lg"
                         style="object-fit:cover;">
                @else
                    <div class="profile-avatar-lg" style="background: {{ $color }};">
                        {{ strtoupper(substr($course->name, 0, 1)) }}
                    </div>
                @endif

                <p class="profile-title">{{ $course->name }}</p>

                <p class="profile-subtitle">
                    {{ $course->course_code ? 'Code: ' . $course->course_code : 'Coaching Course' }}
                </p>

                @if($course->status == 'active')
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
                        <p class="stat-mini-label">Course ID</p>
                        <p class="stat-mini-value">#{{ $course->id }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Fee</p>
                        <p class="stat-mini-value">₹{{ number_format($course->fee, 0) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Duration</p>
                        <p class="stat-mini-value-sm">{{ $course->duration ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Status</p>
                        <p class="stat-mini-value-sm">{{ ucfirst($course->status) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('course_edit')
                    <a href="{{ route('admin.courses.edit', $course->id) }}" class="quick-link primary">
                        <i class="fas fa-book"></i>
                        Edit Course
                    </a>
                @endcan

                <a href="{{ route('admin.courses.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Courses
                </a>

                @can('course_create')
                    <a href="{{ route('admin.courses.create') }}" class="quick-link">
                        <i class="fas fa-plus"></i>
                        Add New Course
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-book"></i>
                </div>

                <p class="detail-section-title">Course Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">ID</span>
                    <span class="detail-value code-pill">#{{ $course->id }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Course Name</span>
                    <span class="detail-value">{{ $course->name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Course Code</span>
                    <span class="detail-value">{{ $course->course_code ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">
                        {{ $course->branch->name ?? 'No Branch' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Duration</span>
                    <span class="detail-value">{{ $course->duration ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Fee</span>
                    <span class="detail-value">₹{{ number_format($course->fee, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Status</span>

                    @if($course->status == 'active')
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

                <div class="detail-row">
                    <span class="detail-label">Created At</span>
                    <span class="detail-value">
                        {{ optional($course->created_at)->format('d M Y, H:i') ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Updated At</span>
                    <span class="detail-value">
                        {{ optional($course->updated_at)->format('d M Y, H:i') ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-align-left"></i>
                </div>

                <p class="detail-section-title">Description</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Description</span>
                    <span class="detail-value">
                        {{ $course->description ?? '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection