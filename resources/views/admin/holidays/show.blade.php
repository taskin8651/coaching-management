
@extends('layouts.admin')

@section('page-title', 'Holiday Details')

@section('content')

@php
    $colors = [
        '#4F46E5', '#0EA5E9', '#10B981', '#F59E0B',
        '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6'
    ];

    $color = $colors[$holiday->id % count($colors)];

    $duration = $holiday->start_date && $holiday->end_date
        ? $holiday->start_date->diffInDays($holiday->end_date) + 1
        : ($holiday->date ? 1 : 0);

    $audiences = $holiday->audiences ?? [];

    if (is_string($audiences)) {
        $audiences = json_decode($audiences, true) ?? [];
    }

    $targetModeLabels = [
        'all_branches' => 'All Branches',
        'branch_wise' => 'Branch Wise',
        'batch_wise' => 'Batch Wise',
        'selected_students' => 'Selected Students',
        'selected_teachers' => 'Selected Teachers',
        'all_branch_teachers' => 'All Branch Teachers',
        'single_branch_teachers' => 'Single Branch Teachers',
        'teacher_wise' => 'Teacher Wise',
        'all_branch_staff' => 'All Branch Staff',
        'single_branch_staff' => 'Single Branch Staff',
        'selected_staff' => 'Selected Staff',
    ];

    $targetTypeLabels = [
        'branch' => 'Branch',
        'branches' => 'Branch',
        'batch' => 'Batch',
        'batches' => 'Batch',
        'student' => 'Student',
        'students' => 'Student',
        'teacher' => 'Teacher',
        'teachers' => 'Teacher',
        'staff' => 'Staff',
    ];

    $targets = $holiday->targets ?? collect();
@endphp

{{-- Page Header --}}
<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.holidays.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Holiday Details</h2>

        <p class="admin-page-subtitle">
            Complete holiday information, audience and targeting details
        </p>
    </div>

    <div class="show-actions">
        @can('holiday_edit')
            <a href="{{ route('admin.holidays.edit', $holiday->id) }}"
               class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Holiday
            </a>
        @endcan

        @can('holiday_delete')
            <form action="{{ route('admin.holidays.destroy', $holiday->id) }}"
                  method="POST"
                  onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                @csrf
                @method('DELETE')

                <button type="submit" class="btn-danger">
                    <i class="fas fa-trash-alt"></i>
                    Delete
                </button>
            </form>
        @endcan
    </div>
</div>

<div class="show-grid">

    {{-- LEFT COLUMN --}}
    <div>

        {{-- Holiday Profile --}}
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background: {{ $color }};">
                    <i class="fas fa-calendar-alt"></i>
                </div>

                <p class="profile-title">{{ $holiday->name }}</p>

                <p class="profile-subtitle">
                    {{ $holiday->category ?: 'General Holiday' }}
                </p>

                @if($holiday->type === 'mandatory')
                    <span class="status-pill success">
                        <i class="fas fa-check-circle"></i>
                        Mandatory Holiday
                    </span>
                @else
                    <span class="status-pill warning">
                        <i class="fas fa-calendar-check"></i>
                        Optional Holiday
                    </span>
                @endif
            </div>

            <div class="detail-section-pad-sm">
                <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">

                    <div class="stat-mini">
                        <p class="stat-mini-label">Holiday ID</p>
                        <p class="stat-mini-value">#{{ $holiday->id }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Duration</p>
                        <p class="stat-mini-value">
                            {{ $duration }} {{ $duration === 1 ? 'Day' : 'Days' }}
                        </p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Targets</p>
                        <p class="stat-mini-value">{{ $targets->count() }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Audience Groups</p>
                        <p class="stat-mini-value">{{ count($audiences) }}</p>
                    </div>

                    <div class="stat-mini" style="grid-column: span 2;">
                        <p class="stat-mini-label">Holiday Period</p>
                        <p class="stat-mini-value-sm">
                            {{ optional($holiday->start_date ?? $holiday->date)->format('d M Y') ?? '-' }}

                            @if($holiday->end_date &&
                                $holiday->start_date &&
                                !$holiday->start_date->isSameDay($holiday->end_date))
                                - {{ $holiday->end_date->format('d M Y') }}
                            @endif
                        </p>
                    </div>

                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('holiday_edit')
                    <a href="{{ route('admin.holidays.edit', $holiday->id) }}"
                       class="quick-link primary">
                        <i class="fas fa-calendar-edit"></i>
                        Edit Holiday
                    </a>
                @endcan

                <a href="{{ route('admin.holidays.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Holidays
                </a>

                @can('holiday_create')
                    <a href="{{ route('admin.holidays.create') }}" class="quick-link">
                        <i class="fas fa-plus-circle"></i>
                        Add New Holiday
                    </a>
                @endcan
            </div>
        </div>

    </div>

    {{-- RIGHT COLUMN --}}
    <div>

        {{-- Holiday Details --}}
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>

                <p class="detail-section-title">Holiday Information</p>
            </div>

            <div class="detail-section-body">

                <div class="detail-row">
                    <span class="detail-label">Holiday ID</span>
                    <span class="detail-value code-pill">#{{ $holiday->id }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Holiday Name</span>
                    <span class="detail-value">{{ $holiday->name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Category</span>
                    <span class="detail-value">{{ $holiday->category ?: '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Holiday Type</span>
                    <span class="detail-value">
                        @if($holiday->type === 'mandatory')
                            <span class="status-pill success">Mandatory</span>
                        @else
                            <span class="status-pill warning">Optional</span>
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Single Date</span>
                    <span class="detail-value">
                        {{ optional($holiday->date)->format('d M Y') ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Start Date</span>
                    <span class="detail-value">
                        {{ optional($holiday->start_date)->format('d M Y') ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">End Date</span>
                    <span class="detail-value">
                        {{ optional($holiday->end_date)->format('d M Y') ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Total Duration</span>
                    <span class="detail-value">{{ $duration }} day(s)</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Description</span>
                    <span class="detail-value" style="max-width:65%; text-align:right;">
                        {{ $holiday->description ?: 'No description provided' }}
                    </span>
                </div>

            </div>
        </div>

        {{-- Branch Information --}}
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-building"></i>
                </div>

                <p class="detail-section-title">Branch Information</p>
            </div>

            <div class="detail-section-body">

                <div class="detail-row">
                    <span class="detail-label">Branch ID</span>
                    <span class="detail-value">
                        {{ $holiday->branch_id ? '#' . $holiday->branch_id : '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch Name</span>
                    <span class="detail-value">
                        {{ $holiday->branch->name ?? 'All Branches' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Targeting Mode</span>
                    <span class="detail-value">
                        {{ $targetModeLabels[$holiday->target_mode] ?? ($holiday->target_mode ? ucfirst(str_replace('_', ' ', $holiday->target_mode)) : '-') }}
                    </span>
                </div>

            </div>
        </div>

        {{-- Audience --}}
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-users"></i>
                </div>

                <p class="detail-section-title">Audience</p>
            </div>

            <div class="detail-section-pad-sm">
                @if(count($audiences))
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($audiences as $audience)
                            <span class="role-tag">
                                <i class="fas fa-user" style="font-size:10px;"></i>
                                {{ ucfirst(str_replace('_', ' ', $audience)) }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="assign-empty">
                        <div class="assign-empty-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <p class="assign-empty-title">No audience assigned</p>
                        <p class="assign-empty-text">
                            No audience groups are saved for this holiday.
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Target Details --}}
        <div class="detail-card mb-3">
            <div class="detail-section-head between">
                <div class="d-flex align-items-center gap-2">
                    <div class="detail-section-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>

                    <p class="detail-section-title">Target Details</p>
                </div>

                <span class="status-pill success">
                    {{ $targets->count() }} selected
                </span>
            </div>

            <div class="detail-section-body">

               
@forelse($targets as $target)
    @php
        $targetType = strtolower($target->target_type);
        $targetId = $target->target_id;
        $targetName = null;

        switch ($targetType) {
            case 'branch':
            case 'branches':
                $record = \App\Models\Branch::find($targetId);
                $targetName = $record->name ?? null;
                break;

            case 'student':
            case 'students':
                $record = \App\Models\Student::with('user')->find($targetId);
                $targetName = $record->user->name
                    ?? $record->name
                    ?? null;
                break;

            case 'teacher':
            case 'teachers':
                $record = \App\Models\Teacher::with('user')->find($targetId);
                $targetName = $record->user->name
                    ?? $record->name
                    ?? null;
                break;

            case 'staff':
            case 'employees':
                $record = \App\Models\Staff::with('user')->find($targetId);
                $targetName = $record->user->name
                    ?? $record->name
                    ?? null;
                break;

            case 'user':
            case 'users':
                $record = \App\Models\User::find($targetId);
                $targetName = $record->name ?? null;
                break;

            default:
                $record = null;
                break;
        }

        $targetTypeLabels = $targetTypeLabels ?? [
            'branch' => 'Branch',
            'branches' => 'Branch',
            'student' => 'Student',
            'students' => 'Student',
            'teacher' => 'Teacher',
            'teachers' => 'Teacher',
            'staff' => 'Staff',
            'employees' => 'Staff',
            'user' => 'User',
            'users' => 'User',
            'batch' => 'Batch',
            'batches' => 'Batch',
        ];
    @endphp

    <div class="detail-row">
        <span class="detail-label">
            {{ $targetTypeLabels[$targetType] ?? ucfirst(str_replace('_', ' ', $targetType)) }}
        </span>

        <span class="detail-value">
            {{ $targetName ?: ($targetId ? 'ID: ' . $targetId : '-') }}
        </span>
    </div>
@empty
    <div class="assign-empty">
        <div class="assign-empty-icon">
            <i class="fas fa-bullseye"></i>
        </div>

        <p class="assign-empty-title">No individual targets</p>

        <p class="assign-empty-text">
            No individual target records are linked to this holiday.
            The targeting mode is
            {{ $targetModeLabels[$holiday->target_mode] ?? 'not specified' }}.
        </p>
    </div>
@endforelse


            </div>
        </div>

        {{-- Created By / Record Information --}}
        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-history"></i>
                </div>

                <p class="detail-section-title">Record Information</p>
            </div>

            <div class="detail-section-body">

                <div class="detail-row">
                    <span class="detail-label">Created By</span>
                    <span class="detail-value">
                        {{ $holiday->createdBy->name ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Creator ID</span>
                    <span class="detail-value">
                        {{ $holiday->created_by_id ? '#' . $holiday->created_by_id : '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Created At</span>
                    <span class="detail-value">
                        {{ optional($holiday->created_at)->format('d M Y, H:i') ?? '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Updated At</span>
                    <span class="detail-value">
                        {{ optional($holiday->updated_at)->format('d M Y, H:i') ?? '-' }}
                    </span>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
