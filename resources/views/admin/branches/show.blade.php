@extends('layouts.admin')

@section('page-title', 'Show Branch')

@section('content')

@php
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
    $color = $colors[$branch->id % count($colors)];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.branches.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Branch Details</h2>

        <p class="admin-page-subtitle">
            Full details for this coaching branch
        </p>
    </div>

    <div class="show-actions">
        @can('branch_edit')
            <a href="{{ route('admin.branches.edit', $branch->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Branch
            </a>
        @endcan

        @can('branch_delete')
            <form action="{{ route('admin.branches.destroy', $branch->id) }}"
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
                @if($branch->logo)
                    <img src="{{ $branch->logo }}"
                         alt="{{ $branch->name }}"
                         class="profile-avatar-lg"
                         style="object-fit:cover;">
                @else
                    <div class="profile-avatar-lg" style="background: {{ $color }};">
                        {{ strtoupper(substr($branch->name, 0, 1)) }}
                    </div>
                @endif

                <p class="profile-title">{{ $branch->name }}</p>

                <p class="profile-subtitle">
                    {{ $branch->branch_code ? 'Code: ' . $branch->branch_code : 'Coaching Branch' }}
                </p>

                @if($branch->status == 'active')
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
                        <p class="stat-mini-label">Branch ID</p>
                        <p class="stat-mini-value">#{{ $branch->id }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Status</p>
                        <p class="stat-mini-value">{{ ucfirst($branch->status) }}</p>
                    </div>

                    <div class="stat-mini" style="grid-column: span 2;">
                        <p class="stat-mini-label">Created On</p>
                        <p class="stat-mini-value-sm">
                            {{ optional($branch->created_at)->format('d M Y') ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('branch_edit')
                    <a href="{{ route('admin.branches.edit', $branch->id) }}" class="quick-link primary">
                        <i class="fas fa-building"></i>
                        Edit Branch
                    </a>
                @endcan

                <a href="{{ route('admin.branches.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Branches
                </a>

                @can('branch_create')
                    <a href="{{ route('admin.branches.create') }}" class="quick-link">
                        <i class="fas fa-plus"></i>
                        Add New Branch
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-building"></i>
                </div>

                <p class="detail-section-title">Branch Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">ID</span>
                    <span class="detail-value code-pill">#{{ $branch->id }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch Name</span>
                    <span class="detail-value">{{ $branch->name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch Code</span>
                    <span class="detail-value">
                        {{ $branch->branch_code ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Manager</span>
                    <span class="detail-value">
                        {{ $branch->manager->name ?? 'Not Assigned' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Status</span>

                    @if($branch->status == 'active')
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
                        {{ optional($branch->created_at)->format('d M Y, H:i') ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Updated At</span>
                    <span class="detail-value">
                        {{ optional($branch->updated_at)->format('d M Y, H:i') ?? '-' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-phone"></i>
                </div>

                <p class="detail-section-title">Contact Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">
                        {{ $branch->phone ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Email</span>

                    <div>
                        <span class="detail-value">{{ $branch->email ?? '-' }}</span>

                        @if($branch->email)
                            <a href="mailto:{{ $branch->email }}" class="send-mail-link">
                                Send Email
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>

                <p class="detail-section-title">Address Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Address</span>
                    <span class="detail-value">
                        {{ $branch->address ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">City</span>
                    <span class="detail-value">
                        {{ $branch->city ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">State</span>
                    <span class="detail-value">
                        {{ $branch->state ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Pincode</span>
                    <span class="detail-value">
                        {{ $branch->pincode ?? '-' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection