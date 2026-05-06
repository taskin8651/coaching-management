@extends('layouts.admin')

@section('page-title', 'Show Batch')

@section('content')

@php
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
    $color = $colors[$batch->id % count($colors)];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.batches.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Batch Details</h2>

        <p class="admin-page-subtitle">
            Full details for this coaching batch
        </p>
    </div>

    <div class="show-actions">
        @can('batch_edit')
            <a href="{{ route('admin.batches.edit', $batch->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Batch
            </a>
        @endcan

        @can('batch_delete')
            <form action="{{ route('admin.batches.destroy', $batch->id) }}"
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
                    {{ strtoupper(substr($batch->name, 0, 1)) }}
                </div>

                <p class="profile-title">{{ $batch->name }}</p>

                <p class="profile-subtitle">
                    {{ $batch->batch_code ? 'Code: ' . $batch->batch_code : 'Coaching Batch' }}
                </p>

                @if($batch->status == 'active')
                    <span class="status-pill success">
                        <i class="fas fa-check-circle"></i>
                        Active
                    </span>
                @elseif($batch->status == 'completed')
                    <span class="status-pill" style="background:#E0F2FE;color:#075985;">
                        <i class="fas fa-check-double"></i>
                        Completed
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
                        <p class="stat-mini-label">Batch ID</p>
                        <p class="stat-mini-value">#{{ $batch->id }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Capacity</p>
                        <p class="stat-mini-value">{{ $batch->max_students ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Branch</p>
                        <p class="stat-mini-value-sm">{{ $batch->branch->name ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Course</p>
                        <p class="stat-mini-value-sm">{{ $batch->course->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('batch_edit')
                    <a href="{{ route('admin.batches.edit', $batch->id) }}" class="quick-link primary">
                        <i class="fas fa-layer-group"></i>
                        Edit Batch
                    </a>
                @endcan

                <a href="{{ route('admin.batches.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Batches
                </a>

                @can('batch_create')
                    <a href="{{ route('admin.batches.create') }}" class="quick-link">
                        <i class="fas fa-plus"></i>
                        Add New Batch
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-layer-group"></i>
                </div>

                <p class="detail-section-title">Batch Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">ID</span>
                    <span class="detail-value code-pill">#{{ $batch->id }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Batch Name</span>
                    <span class="detail-value">{{ $batch->name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Batch Code</span>
                    <span class="detail-value">{{ $batch->batch_code ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">{{ $batch->branch->name ?? 'No Branch' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Course</span>
                    <span class="detail-value">{{ $batch->course->name ?? 'No Course' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Max Students</span>
                    <span class="detail-value">{{ $batch->max_students ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Status</span>

                    @if($batch->status == 'active')
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-success"></i>
                            <span class="detail-value" style="color:#166534;">Active</span>
                        </div>
                    @elseif($batch->status == 'completed')
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-double" style="color:#075985;"></i>
                            <span class="detail-value" style="color:#075985;">Completed</span>
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
                        {{ optional($batch->created_at)->format('d M Y, H:i') ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Updated At</span>
                    <span class="detail-value">
                        {{ optional($batch->updated_at)->format('d M Y, H:i') ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>

                <p class="detail-section-title">Schedule Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Start Date</span>
                    <span class="detail-value">
                        {{ optional($batch->start_date)->format('d M Y') ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">End Date</span>
                    <span class="detail-value">
                        {{ optional($batch->end_date)->format('d M Y') ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Start Time</span>
                    <span class="detail-value">
                        {{ $batch->start_time ? \Carbon\Carbon::parse($batch->start_time)->format('h:i A') : '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">End Time</span>
                    <span class="detail-value">
                        {{ $batch->end_time ? \Carbon\Carbon::parse($batch->end_time)->format('h:i A') : '-' }}
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
                        {{ $batch->description ?? '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection