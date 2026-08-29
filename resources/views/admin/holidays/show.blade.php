@extends('layouts.admin')

@section('page-title', 'Show Holiday')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.holidays.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">{{ $holiday->name }}</h2>
        <p class="admin-page-subtitle">{{ optional($holiday->date)->format('d M Y (l)') }}</p>
    </div>

    <div class="show-actions">
        @can('holiday_edit')
            <a href="{{ route('admin.holidays.edit', $holiday->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i> Edit Holiday
            </a>
        @endcan
    </div>
</div>

<div class="show-grid">
    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background:#F59E0B;">
                    <i class="fas fa-umbrella-beach"></i>
                </div>
                <p class="profile-title">{{ $holiday->name }}</p>
                <p class="profile-subtitle">{{ $holiday->branch->name ?? 'All Branches' }}</p>

                @if($holiday->type == 'mandatory')
                    <span class="status-pill success">Mandatory</span>
                @else
                    <span class="status-pill warning">Optional</span>
                @endif
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>
            <div class="quick-list">
                <a href="{{ route('admin.holidays.index') }}" class="quick-link"><i class="fas fa-list"></i> All Holidays</a>
                @can('holiday_create')
                    <a href="{{ route('admin.holidays.create') }}" class="quick-link"><i class="fas fa-plus"></i> Add New Holiday</a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-info-circle"></i></div>
                <p class="detail-section-title">Holiday Information</p>
            </div>
            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Date</span><span class="detail-value">{{ optional($holiday->date)->format('d M Y (l)') }}</span></div>
                <div class="detail-row"><span class="detail-label">Branch</span><span class="detail-value">{{ $holiday->branch->name ?? 'All Branches' }}</span></div>
                <div class="detail-row"><span class="detail-label">Type</span><span class="detail-value">{{ ucfirst($holiday->type) }}</span></div>
                <div class="detail-row"><span class="detail-label">Description</span><span class="detail-value">{{ $holiday->description ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Added By</span><span class="detail-value">{{ $holiday->createdBy->name ?? '-' }}</span></div>
            </div>
        </div>
    </div>
</div>

@endsection
