@extends('layouts.admin')

@section('page-title', 'Show Fee Structure')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-structures.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">{{ $feeStructure->title }}</h2>
        <p class="admin-page-subtitle">
            Fee structure details and breakdown
        </p>
    </div>

    @can('fee_structure_edit')
        <a href="{{ route('admin.fee-structures.edit', $feeStructure->id) }}" class="btn-primary">
            <i class="fas fa-pencil-alt"></i>
            Edit Fee Structure
        </a>
    @endcan
</div>

<div class="show-grid">

    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background:#10B981;">
                    <i class="fas fa-rupee-sign"></i>
                </div>

                <p class="profile-title">{{ $feeStructure->title }}</p>
                <p class="profile-subtitle">{{ $feeStructure->course->name ?? '-' }}</p>

                @if($feeStructure->status == 'active')
                    <span class="status-pill success">Active</span>
                @else
                    <span class="status-pill warning">Inactive</span>
                @endif
            </div>

            <div class="detail-section-pad-sm">
                <div class="d-grid gap-2" style="grid-template-columns: 1fr;">
                    <div class="stat-mini">
                        <p class="stat-mini-label">Total Fee</p>
                        <p class="stat-mini-value-sm">₹{{ number_format($feeStructure->total_fee, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('fee_structure_edit')
                    <a href="{{ route('admin.fee-structures.edit', $feeStructure->id) }}" class="quick-link primary">
                        <i class="fas fa-pencil-alt"></i>
                        Edit Fee Structure
                    </a>
                @endcan

                <a href="{{ route('admin.fee-structures.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Fee Structures
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

                <p class="detail-section-title">Basic Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Title</span>
                    <span class="detail-value">{{ $feeStructure->title }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">{{ $feeStructure->branch->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Course</span>
                    <span class="detail-value">{{ $feeStructure->course->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Batch</span>
                    <span class="detail-value">{{ $feeStructure->batch->name ?? 'All Batches' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">{{ ucfirst($feeStructure->status) }}</span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>

                <p class="detail-section-title">Fee Breakdown</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Admission Fee</span>
                    <span class="detail-value">₹{{ number_format($feeStructure->admission_fee, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Tuition Fee</span>
                    <span class="detail-value">₹{{ number_format($feeStructure->tuition_fee, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Exam Fee</span>
                    <span class="detail-value">₹{{ number_format($feeStructure->exam_fee, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Material Fee</span>
                    <span class="detail-value">₹{{ number_format($feeStructure->material_fee, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Other Fee</span>
                    <span class="detail-value">₹{{ number_format($feeStructure->other_fee, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Total Fee</span>
                    <span class="detail-value">
                        <strong>₹{{ number_format($feeStructure->total_fee, 2) }}</strong>
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
                    <span class="detail-value">{{ $feeStructure->description ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection