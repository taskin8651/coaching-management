@extends('layouts.admin')

@section('page-title', 'Show Subject')

@section('content')

@php
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
    $color = $colors[$subject->id % count($colors)];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.subjects.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Subject Details</h2>

        <p class="admin-page-subtitle">
            Full details for this coaching subject
        </p>
    </div>

    <div class="show-actions">
        @can('subject_edit')
            <a href="{{ route('admin.subjects.edit', $subject->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Subject
            </a>
        @endcan

        @can('subject_delete')
            <form action="{{ route('admin.subjects.destroy', $subject->id) }}"
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
                <div class="profile-avatar-lg" style="background: {{ $color }};">
                    {{ strtoupper(substr($subject->name, 0, 1)) }}
                </div>

                <p class="profile-title">{{ $subject->name }}</p>

                <p class="profile-subtitle">
                    {{ $subject->subject_code ? 'Code: ' . $subject->subject_code : 'Coaching Subject' }}
                </p>

                @if($subject->status == 'active')
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
                        <p class="stat-mini-label">Subject ID</p>
                        <p class="stat-mini-value">#{{ $subject->id }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Status</p>
                        <p class="stat-mini-value-sm">{{ ucfirst($subject->status) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Branch</p>
                        <p class="stat-mini-value-sm">{{ $subject->branch->name ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Course</p>
                        <p class="stat-mini-value-sm">{{ $subject->course->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('subject_edit')
                    <a href="{{ route('admin.subjects.edit', $subject->id) }}" class="quick-link primary">
                        <i class="fas fa-book-open"></i>
                        Edit Subject
                    </a>
                @endcan

                <a href="{{ route('admin.subjects.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Subjects
                </a>

                @can('subject_create')
                    <a href="{{ route('admin.subjects.create') }}" class="quick-link">
                        <i class="fas fa-plus"></i>
                        Add New Subject
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-book-open"></i>
                </div>

                <p class="detail-section-title">Subject Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">ID</span>
                    <span class="detail-value code-pill">#{{ $subject->id }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Subject Name</span>
                    <span class="detail-value">{{ $subject->name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Subject Code</span>
                    <span class="detail-value">{{ $subject->subject_code ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">
                        {{ $subject->branch->name ?? 'No Branch' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Course</span>
                    <span class="detail-value">
                        {{ $subject->course->name ?? 'No Course' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Status</span>

                    @if($subject->status == 'active')
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
                        {{ optional($subject->created_at)->format('d M Y, H:i') ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Updated At</span>
                    <span class="detail-value">
                        {{ optional($subject->updated_at)->format('d M Y, H:i') ?? '-' }}
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
                        {{ $subject->description ?? '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection