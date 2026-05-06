@extends('layouts.admin')

@section('page-title', 'Show Staff')

@section('content')

@php
    $name = $staff->user->name ?? 'Staff';
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
    $color = $colors[$staff->id % count($colors)];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.staff.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Staff Details</h2>

        <p class="admin-page-subtitle">
            Full details for this branch staff member
        </p>
    </div>

    <div class="show-actions">
        @can('staff_edit')
            <a href="{{ route('admin.staff.edit', $staff->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Staff
            </a>
        @endcan

        @can('staff_delete')
            <form action="{{ route('admin.staff.destroy', $staff->id) }}"
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
                @if($staff->photo)
                    <img src="{{ $staff->photo }}"
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
                    {{ $staff->designation ?? 'Branch Staff' }}
                </p>

                @if($staff->status == 'active')
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
                        <p class="stat-mini-label">Staff ID</p>
                        <p class="stat-mini-value">#{{ $staff->id }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Salary</p>
                        <p class="stat-mini-value">₹{{ number_format($staff->salary, 0) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Branch</p>
                        <p class="stat-mini-value-sm">{{ $staff->branch->name ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Joining</p>
                        <p class="stat-mini-value-sm">
                            {{ optional($staff->joining_date)->format('d M Y') ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('staff_edit')
                    <a href="{{ route('admin.staff.edit', $staff->id) }}" class="quick-link primary">
                        <i class="fas fa-user-tie"></i>
                        Edit Staff
                    </a>
                @endcan

                <a href="{{ route('admin.staff.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Staff
                </a>

                @can('staff_create')
                    <a href="{{ route('admin.staff.create') }}" class="quick-link">
                        <i class="fas fa-plus"></i>
                        Add New Staff
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

                <p class="detail-section-title">Staff Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">ID</span>
                    <span class="detail-value code-pill">#{{ $staff->id }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">{{ $staff->user->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ $staff->user->email ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">{{ $staff->branch->name ?? 'No Branch' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">{{ $staff->phone ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Alternate Phone</span>
                    <span class="detail-value">{{ $staff->alternate_phone ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Status</span>

                    @if($staff->status == 'active')
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

                <p class="detail-section-title">Job Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Designation</span>
                    <span class="detail-value">{{ $staff->designation ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Department</span>
                    <span class="detail-value">{{ $staff->department ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Salary</span>
                    <span class="detail-value">₹{{ number_format($staff->salary, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Joining Date</span>
                    <span class="detail-value">
                        {{ optional($staff->joining_date)->format('d M Y') ?? '-' }}
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
                    <span class="detail-value">{{ $staff->address ?? '-' }}</span>
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
                @if($staff->documents && count($staff->documents))
                    @foreach($staff->documents as $document)
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