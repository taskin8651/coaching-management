@extends('layouts.admin')

@section('page-title', 'Substitute Teacher Details')

@section('content')

@php
    $item = $timetableSubstitution;
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.timetable-substitutions.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Substitute Teacher Details</h2>
        <p class="admin-page-subtitle">Full assignment details and timetable reference.</p>
    </div>

    <div class="show-actions">
        <a href="{{ route('admin.timetable-substitutions.edit', $item->id) }}" class="btn-primary">
            <i class="fas fa-pencil-alt"></i>
            Edit
        </a>
    </div>
</div>

<div class="show-grid">
    <div class="detail-card mb-3">
        <div class="profile-hero">
            <div class="profile-avatar-lg" style="background:#4F46E5;">
                <i class="fas fa-user-clock"></i>
            </div>

            <p class="profile-title">{{ $item->substituteTeacher->user->name ?? 'Substitute Teacher' }}</p>
            <p class="profile-subtitle">
                {{ optional($item->substitution_date)->format('d M Y') ?? '-' }}
            </p>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-calendar-alt"></i></div>
                <p class="detail-section-title">Timetable Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Branch</span><span class="detail-value">{{ $item->timetable->branch->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Course</span><span class="detail-value">{{ $item->timetable->course->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Batch</span><span class="detail-value">{{ $item->timetable->batch->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Subject</span><span class="detail-value">{{ $item->timetable->subject->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Day / Date</span><span class="detail-value">{{ $item->timetable->schedule_date ? $item->timetable->schedule_date->format('d M Y') : ($item->timetable->day_of_week ?? '-') }}</span></div>
                <div class="detail-row"><span class="detail-label">Time</span><span class="detail-value">{{ $item->timetable->start_time ?? '-' }} - {{ $item->timetable->end_time ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Room</span><span class="detail-value">{{ $item->timetable->room ?? '-' }}</span></div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-exchange-alt"></i></div>
                <p class="detail-section-title">Substitution Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Original Teacher</span><span class="detail-value">{{ $item->originalTeacher->user->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Substitute Teacher</span><span class="detail-value">{{ $item->substituteTeacher->user->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Substitution Date</span><span class="detail-value">{{ optional($item->substitution_date)->format('d M Y') ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Reason</span><span class="detail-value">{{ $item->reason ?: '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Change Note</span><span class="detail-value">{{ $item->change_note ?: '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Changed By</span><span class="detail-value">{{ $item->changedBy->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Created At</span><span class="detail-value">{{ optional($item->created_at)->format('d M Y, H:i') ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Updated At</span><span class="detail-value">{{ optional($item->updated_at)->format('d M Y, H:i') ?? '-' }}</span></div>
            </div>
        </div>
    </div>
</div>

@endsection
