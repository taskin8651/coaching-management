@extends('layouts.admin')

@section('page-title', 'Show Fee Head')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-heads.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">{{ $feeHead->name }}</h2>
        <p class="admin-page-subtitle">Fee head details</p>
    </div>

    <div class="show-actions">
        @can('fee_master_edit')
            <a href="{{ route('admin.fee-heads.edit', $feeHead->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i> Edit Fee Head
            </a>
        @endcan
    </div>
</div>

<div class="show-grid">
    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background:#4F46E5;">
                    {{ strtoupper(substr($feeHead->name, 0, 1)) }}
                </div>
                <p class="profile-title">{{ $feeHead->name }}</p>
                <p class="profile-subtitle">{{ $feeHead->code }}</p>

                @if($feeHead->status == 'active')
                    <span class="status-pill success">Active</span>
                @else
                    <span class="status-pill warning">Inactive</span>
                @endif
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>
            <div class="quick-list">
                <a href="{{ route('admin.fee-heads.index') }}" class="quick-link"><i class="fas fa-list"></i> All Fee Heads</a>
                @can('fee_master_create')
                    <a href="{{ route('admin.fee-heads.create') }}" class="quick-link"><i class="fas fa-plus"></i> Add New Fee Head</a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-info-circle"></i></div>
                <p class="detail-section-title">Fee Head Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Code</span><span class="detail-value code-pill">{{ $feeHead->code }}</span></div>
                <div class="detail-row"><span class="detail-label">Name</span><span class="detail-value">{{ $feeHead->name }}</span></div>
                <div class="detail-row"><span class="detail-label">GST Applicable</span><span class="detail-value">{{ $feeHead->gst_applicable ? 'Yes' : 'No' }}</span></div>
                <div class="detail-row"><span class="detail-label">Default GST %</span><span class="detail-value">{{ number_format($feeHead->default_gst_percent, 2) }}%</span></div>
                <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">{{ ucfirst($feeHead->status) }}</span></div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-align-left"></i></div>
                <p class="detail-section-title">Description</p>
            </div>
            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-value">{{ $feeHead->description ?? '-' }}</span></div>
            </div>
        </div>
    </div>
</div>

@endsection
