@extends('layouts.admin')

@section('page-title', 'Show Homework')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.homeworks.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">
            {{ $homework->title }}
            @if($homework->approval_status == 'approved')
                <span class="status-pill success">Approved</span>
            @elseif($homework->approval_status == 'rejected')
                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Rejected</span>
            @else
                <span class="status-pill warning">Pending Approval</span>
            @endif
        </h2>

        <p class="admin-page-subtitle">
            Homework details, attachments and student submission tracking
        </p>
    </div>

    <div class="show-actions">
        @can('homework_edit')
            <a href="{{ route('admin.homeworks.edit', $homework->id) }}" class="btn-outline btn-outline-edit">
                <i class="fas fa-pencil-alt"></i>
                Edit Homework
            </a>
        @endcan

        @can('homework_approve')
            @if($homework->approval_status !== 'approved')
                <form action="{{ route('admin.homeworks.approve', $homework->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-check"></i>
                        Approve Homework
                    </button>
                </form>
            @endif
        @endcan
    </div>
</div>

@php
    $totalSubs = $homework->submissions->count();
    $completedSubs = $homework->submissions->whereIn('status', ['completed', 'submitted'])->count();
    $pendingSubs = $homework->submissions->whereIn('status', ['pending', 'incomplete'])->count();
@endphp

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Assigned Students</p>
        <p class="stat-value">{{ $totalSubs }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Completed</p>
        <p class="stat-value">{{ $completedSubs }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Pending</p>
        <p class="stat-value">{{ $pendingSubs }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Attachments</p>
        <p class="stat-value">{{ count($homework->attachments) }}</p>
    </div>
</div>

<div class="detail-card mb-3">
    <div class="detail-section-head">
        <div class="detail-section-icon">
            <i class="fas fa-info-circle"></i>
        </div>

        <p class="detail-section-title">Homework Information</p>
    </div>

    <div class="detail-section-body">
        <div class="detail-row">
            <span class="detail-label">Branch</span>
            <span class="detail-value">{{ $homework->branch->name ?? '-' }}</span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Batch</span>
            <span class="detail-value">{{ $homework->batch->name ?? '-' }}</span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Subject</span>
            <span class="detail-value">{{ $homework->subject->name ?? '-' }}</span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Teacher</span>
            <span class="detail-value">{{ $homework->teacher->user->name ?? '-' }}</span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Homework Date</span>
            <span class="detail-value">
                {{ $homework->homework_date ? \Carbon\Carbon::parse($homework->homework_date)->format('d M Y') : '-' }}
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Due Date</span>
            <span class="detail-value">
                {{ $homework->due_date ? \Carbon\Carbon::parse($homework->due_date)->format('d M Y') : '-' }}
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value">
                @if($homework->status == 'active')
                    <span class="status-pill success">Active</span>
                @elseif($homework->status == 'closed')
                    <span class="status-pill" style="background:#F1F5F9;color:#475569;">Closed</span>
                @else
                    <span class="status-pill" style="background:#F1F5F9;color:#475569;">{{ ucfirst($homework->status ?? '-') }}</span>
                @endif
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Approval</span>
            <span class="detail-value">
                @if($homework->approval_status == 'approved')
                    <span class="status-pill success">Approved</span>
                    by {{ $homework->approvedBy->name ?? '-' }}
                    on {{ optional($homework->approved_at)->format('d M Y h:i A') ?? '-' }}
                @elseif($homework->approval_status == 'rejected')
                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Rejected</span>
                @else
                    <span class="status-pill warning">Pending Approval</span>
                @endif
            </span>
        </div>

        <div class="detail-row" style="grid-column:1/-1;">
            <span class="detail-label">Details</span>
            <span class="detail-value">{{ $homework->details ?: '-' }}</span>
        </div>
    </div>
</div>

<div class="page-card mb-3">
    <div class="page-card-header">
        <p class="page-card-title">Attachments</p>
        <span class="page-card-note">
            <i class="fas fa-paperclip"></i>
            {{ count($homework->attachments) }} file(s)
        </span>
    </div>

    <div style="padding:20px;">
        @if($homework->attachments && count($homework->attachments))
            <div class="tag-wrap">
                @foreach($homework->attachments as $file)
                    <a href="{{ $file['url'] }}" target="_blank" class="role-tag">
                        <i class="fas fa-download"></i>
                        {{ $file['name'] }}
                    </a>
                @endforeach
            </div>
        @else
            <p class="field-hint" style="margin:0;">
                <i class="fas fa-info-circle"></i>
                No attachments uploaded for this homework.
            </p>
        @endif
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">Student Submissions</p>
        <span class="page-card-note">
            <i class="fas fa-users"></i>
            {{ $totalSubs }} student(s)
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-HomeworkSubs">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Status</th>
                    <th>Submitted At</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($homework->submissions as $sub)
                    <tr>
                        <td>{{ $sub->student->user->name ?? '-' }}</td>
                        <td>
                            @if(in_array($sub->status, ['completed', 'submitted']))
                                <span class="status-pill success">{{ ucfirst($sub->status) }}</span>
                            @elseif($sub->status == 'incomplete')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Incomplete</span>
                            @else
                                <span class="status-pill warning">{{ ucfirst($sub->status ?? 'Pending') }}</span>
                            @endif
                        </td>
                        <td>{{ optional($sub->submitted_at)->format('d M Y h:i A') ?? '-' }}</td>
                        <td>{{ $sub->remarks ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="empty-line">No students assigned yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
