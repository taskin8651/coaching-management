@extends('layouts.admin')

@section('page-title', 'Show Faculty Log Book')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.faculty-log-books.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>


    <h2 class="admin-page-title">
        Faculty Log Book #{{ $faculty_log_book->id }}
    </h2>

    <p class="admin-page-subtitle">
        Lecture details, timing, teaching information and approval status
    </p>
</div>

<div class="show-actions">

    @can('faculty_log_edit')
        <a href="{{ route('admin.faculty-log-books.edit', $faculty_log_book->id) }}"
           class="btn-primary">
            <i class="fas fa-pencil-alt"></i>
            Edit Log Book
        </a>
    @endcan

</div>


</div>

<div class="show-grid">


{{-- LEFT COLUMN --}}
<div>

    {{-- MAIN STATUS CARD --}}
    <div class="detail-card mb-3">

        <div class="profile-hero">

            <div class="profile-avatar-lg"
                 style="background:#4F46E5;">
                <i class="fas fa-book-open"></i>
            </div>

            <div>
                <p class="profile-title">
                    Faculty Log Book #{{ $faculty_log_book->id }}
                </p>

                <p class="profile-subtitle">
                    {{ $faculty_log_book->teacher->user->name ?? 'Teacher' }}
                </p>

                @php
                    $approvalStatus = strtolower($faculty_log_book->approval_status ?? 'pending');
                @endphp

                @if($approvalStatus === 'approved')
                    <span class="status-pill success">Approved</span>
                @elseif($approvalStatus === 'rejected')
                    <span class="status-pill warning">Rejected</span>
                @else
                    <span class="status-pill warning">Pending Approval</span>
                @endif
            </div>

        </div>


        <div class="detail-section-pad-sm">

            <div class="d-grid gap-2"
                 style="grid-template-columns:1fr 1fr;">

                <div class="stat-mini">
                    <p class="stat-mini-label">Teacher</p>
                    <p class="stat-mini-value-sm">
                        {{ $faculty_log_book->teacher->user->name ?? '-' }}
                    </p>
                </div>

                <div class="stat-mini">
                    <p class="stat-mini-label">Branch</p>
                    <p class="stat-mini-value-sm">
                        {{ $faculty_log_book->branch->name ?? '-' }}
                    </p>
                </div>

                <div class="stat-mini">
                    <p class="stat-mini-label">Batch</p>
                    <p class="stat-mini-value-sm">
                        {{ $faculty_log_book->batch->name ?? '-' }}
                    </p>
                </div>

                <div class="stat-mini">
                    <p class="stat-mini-label">Subject</p>
                    <p class="stat-mini-value-sm">
                        {{ $faculty_log_book->subject->name ?? '-' }}
                    </p>
                </div>

            </div>

        </div>

    </div>


    {{-- QUICK ACTIONS --}}
    <div class="detail-card detail-card-pad">

        <p class="quick-title">Quick Actions</p>

        <div class="quick-list">

            @can('faculty_log_book_edit')
                <a href="{{ route('admin.faculty-log-books.edit', $faculty_log_book->id) }}"
                   class="quick-link primary">
                    <i class="fas fa-pencil-alt"></i>
                    Edit Log Book
                </a>
            @endcan


            @if($approvalStatus !== 'approved')
                @can('faculty_log_book_approve')

                    <form method="POST"
                          action="{{ route('admin.faculty-log-books.approve', $faculty_log_book->id) }}"
                          style="margin:0;">
                        @csrf

                        <button type="submit"
                                class="quick-link"
                                style="width:100%;border:0;background:none;text-align:left;cursor:pointer;">
                            <i class="fas fa-check-circle"></i>
                            Approve Log Book
                        </button>
                    </form>

                @endcan
            @endif


            <a href="{{ route('admin.faculty-log-books.index') }}"
               class="quick-link">
                <i class="fas fa-list"></i>
                All Faculty Log Books
            </a>

        </div>

    </div>

</div>


{{-- RIGHT COLUMN --}}
<div>

    {{-- LECTURE INFORMATION --}}
    <div class="detail-card mb-3">

        <div class="detail-section-head">

            <div class="detail-section-icon">
                <i class="fas fa-info-circle"></i>
            </div>

            <p class="detail-section-title">
                Lecture Information
            </p>

        </div>


        <div class="detail-section-body">

            <div class="detail-row">
                <span class="detail-label">Teacher</span>
                <span class="detail-value">
                    {{ $faculty_log_book->teacher->user->name ?? '-' }}
                </span>
            </div>


            <div class="detail-row">
                <span class="detail-label">Branch</span>
                <span class="detail-value">
                    {{ $faculty_log_book->branch->name ?? '-' }}
                </span>
            </div>


            <div class="detail-row">
                <span class="detail-label">Batch</span>
                <span class="detail-value">
                    {{ $faculty_log_book->batch->name ?? '-' }}
                </span>
            </div>


            <div class="detail-row">
                <span class="detail-label">Subject</span>
                <span class="detail-value">
                    {{ $faculty_log_book->subject->name ?? '-' }}
                </span>
            </div>


            <div class="detail-row">
                <span class="detail-label">Lecture Date</span>
                <span class="detail-value">
                    @if($faculty_log_book->lecture_date)
                        {{ \Carbon\Carbon::parse($faculty_log_book->lecture_date)->format('d M Y') }}
                    @else
                        -
                    @endif
                </span>
            </div>


            <div class="detail-row">
                <span class="detail-label">Log Status</span>
                <span class="detail-value">
                    {{ ucfirst(str_replace('_', ' ', $faculty_log_book->log_status ?? '-')) }}
                </span>
            </div>

        </div>

    </div>


    {{-- SCHEDULED & ACTUAL TIME --}}
    <div class="detail-card mb-3">

        <div class="detail-section-head">

            <div class="detail-section-icon">
                <i class="fas fa-clock"></i>
            </div>

            <p class="detail-section-title">
                Lecture Timing
            </p>

        </div>


        <div class="detail-section-body">

            <div class="detail-row">
                <span class="detail-label">Scheduled Start</span>
                <span class="detail-value">
                    {{ $faculty_log_book->scheduled_start_time ?? '-' }}
                </span>
            </div>


            <div class="detail-row">
                <span class="detail-label">Scheduled End</span>
                <span class="detail-value">
                    {{ $faculty_log_book->scheduled_end_time ?? '-' }}
                </span>
            </div>


            <div class="detail-row">
                <span class="detail-label">Scheduled Minutes</span>
                <span class="detail-value">
                    {{ $faculty_log_book->scheduled_minutes ?? 0 }} minutes
                </span>
            </div>


            <div class="detail-row">
                <span class="detail-label">Actual Start</span>
                <span class="detail-value">
                    {{ $faculty_log_book->actual_start_time ?? '-' }}
                </span>
            </div>


            <div class="detail-row">
                <span class="detail-label">Actual End</span>
                <span class="detail-value">
                    {{ $faculty_log_book->actual_end_time ?? '-' }}
                </span>
            </div>


            <div class="detail-row">
                <span class="detail-label">Actual Time Source</span>
                <span class="detail-value">
                    @if($faculty_log_book->is_actual_verified)
                        <span style="color:#059669;"><i class="fas fa-check-circle"></i> Verified from attendance punch</span>
                    @else
                        <span style="color:#D97706;"><i class="fas fa-clock"></i> Estimated from timetable (attendance not punched out yet)</span>
                    @endif
                </span>
            </div>


            <div class="detail-row">
                <span class="detail-label">Salary Minutes</span>
                <span class="detail-value">
                    {{ $faculty_log_book->salary_minutes ?? 0 }} minutes
                </span>
            </div>

        </div>

    </div>


    {{-- TEACHING DETAILS --}}
    <div class="detail-card mb-3">

        <div class="detail-section-head">

            <div class="detail-section-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>

            <p class="detail-section-title">
                Teaching Details
            </p>

        </div>


        <div class="detail-section-body">

            <div class="detail-row">

                <span class="detail-label">
                    Topic Taught
                </span>

                <span class="detail-value">
                    {{ $faculty_log_book->topic_taught ?? '-' }}
                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Remarks
                </span>

                <span class="detail-value">
                    {{ $faculty_log_book->remarks ?? '-' }}
                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Salary Eligible
                </span>

                <span class="detail-value">

                    @if($faculty_log_book->is_salary_eligible)
                        <span class="status-pill success">
                            Yes
                        </span>
                    @else
                        <span class="status-pill warning">
                            No
                        </span>
                    @endif

                </span>

            </div>

        </div>

    </div>


    {{-- APPROVAL DETAILS --}}
    <div class="detail-card">

        <div class="detail-section-head">

            <div class="detail-section-icon">
                <i class="fas fa-check-circle"></i>
            </div>

            <p class="detail-section-title">
                Approval Details
            </p>

        </div>


        <div class="detail-section-body">

            <div class="detail-row">

                <span class="detail-label">
                    Approval Status
                </span>

                <span class="detail-value">

                    @if($approvalStatus === 'approved')

                        <span class="status-pill success">
                            Approved
                        </span>

                    @elseif($approvalStatus === 'rejected')

                        <span class="status-pill warning">
                            Rejected
                        </span>

                    @else

                        <span class="status-pill warning">
                            Pending
                        </span>

                    @endif

                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Approved By
                </span>

                <span class="detail-value">
                    {{ $faculty_log_book->approvedBy->name ?? '-' }}
                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Approved At
                </span>

                <span class="detail-value">

                    @if($faculty_log_book->approved_at)
                        {{ \Carbon\Carbon::parse($faculty_log_book->approved_at)->format('d M Y, h:i A') }}
                    @else
                        -
                    @endif

                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Created At
                </span>

                <span class="detail-value">

                    @if($faculty_log_book->created_at)
                        {{ $faculty_log_book->created_at->format('d M Y, h:i A') }}
                    @else
                        -
                    @endif

                </span>

            </div>

        </div>

    </div>

</div>


</div>

@endsection
