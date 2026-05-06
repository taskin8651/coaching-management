@extends('layouts.admin')

@section('page-title', 'Show Notice')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.notices.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">{{ $notice->title }}</h2>

        <p class="admin-page-subtitle">
            Notice details, target audience and attachments
        </p>
    </div>

    <div class="show-actions">
        @can('notice_edit')
            <a href="{{ route('admin.notices.edit', $notice->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Notice
            </a>
        @endcan

        @can('notice_delete')
            <form action="{{ route('admin.notices.destroy', $notice->id) }}"
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
                <div class="profile-avatar-lg" style="background:#F59E0B;">
                    <i class="fas fa-bullhorn"></i>
                </div>

                <p class="profile-title">{{ $notice->title }}</p>

                <p class="profile-subtitle">
                    {{ $notice->notice_type ?? 'Notice / Announcement' }}
                </p>

                @if($notice->status == 'published')
                    <span class="status-pill success">Published</span>
                @elseif($notice->status == 'draft')
                    <span class="status-pill warning">Draft</span>
                @else
                    <span class="status-pill" style="background:#F1F5F9;color:#475569;">Inactive</span>
                @endif
            </div>

            <div class="detail-section-pad-sm">
                <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <div class="stat-mini">
                        <p class="stat-mini-label">Notice ID</p>
                        <p class="stat-mini-value">#{{ $notice->id }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Attachments</p>
                        <p class="stat-mini-value">{{ count($notice->attachments) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Audience</p>
                        <p class="stat-mini-value-sm">
                            {{ ucwords(str_replace('_', ' ', $notice->target_audience)) }}
                        </p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Created By</p>
                        <p class="stat-mini-value-sm">{{ $notice->createdBy->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('notice_edit')
                    <a href="{{ route('admin.notices.edit', $notice->id) }}" class="quick-link primary">
                        <i class="fas fa-pencil-alt"></i>
                        Edit Notice
                    </a>
                @endcan

                <a href="{{ route('admin.notices.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Notices
                </a>

                @can('notice_create')
                    <a href="{{ route('admin.notices.create') }}" class="quick-link">
                        <i class="fas fa-plus"></i>
                        Add New Notice
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-info-circle"></i>
                </div>

                <p class="detail-section-title">Notice Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Title</span>
                    <span class="detail-value">{{ $notice->title }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Notice Type</span>
                    <span class="detail-value">{{ $notice->notice_type ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Target Audience</span>
                    <span class="detail-value">
                        {{ ucwords(str_replace('_', ' ', $notice->target_audience)) }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">{{ $notice->branch->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Course</span>
                    <span class="detail-value">{{ $notice->course->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Batch</span>
                    <span class="detail-value">{{ $notice->batch->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Created By</span>
                    <span class="detail-value">{{ $notice->createdBy->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Publish Date</span>
                    <span class="detail-value">
                        {{ $notice->publish_date ? \Carbon\Carbon::parse($notice->publish_date)->format('d M Y') : '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Expiry Date</span>
                    <span class="detail-value">
                        {{ $notice->expiry_date ? \Carbon\Carbon::parse($notice->expiry_date)->format('d M Y') : '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Description</span>
                    <span class="detail-value">{{ $notice->description ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-file"></i>
                </div>

                <p class="detail-section-title">Attachments</p>
            </div>

            <div class="detail-section-body">
                @if($notice->attachments && count($notice->attachments))
                    @foreach($notice->attachments as $file)
                        <div class="detail-row">
                            <span class="detail-label">File</span>
                            <span class="detail-value">
                                <a href="{{ $file['url'] }}" target="_blank">
                                    <i class="fas fa-download"></i>
                                    {{ $file['name'] }}
                                </a>
                            </span>
                        </div>
                    @endforeach
                @else
                    <div class="detail-row">
                        <span class="detail-label">Files</span>
                        <span class="detail-value">No attachments uploaded.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection