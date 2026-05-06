@extends('layouts.admin')

@section('page-title', 'Show Enquiry')

@section('content')

@php
    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
    $color = $colors[$enquiry->id % count($colors)];
@endphp

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.enquiries.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Enquiry Details</h2>

        <p class="admin-page-subtitle">
            View enquiry details, add follow-up and check complete follow-up history
        </p>
    </div>

    <div class="show-actions">
        @can('enquiry_edit')
            <a href="{{ route('admin.enquiries.edit', $enquiry->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Enquiry
            </a>
        @endcan

        @can('enquiry_delete')
            <form action="{{ route('admin.enquiries.destroy', $enquiry->id) }}"
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
                <div class="profile-avatar-lg" style="background: {{ $color }};">
                    {{ strtoupper(substr($enquiry->student_name, 0, 1)) }}
                </div>

                <p class="profile-title">{{ $enquiry->student_name }}</p>

                <p class="profile-subtitle">
                    {{ $enquiry->phone }}
                </p>

                @if($enquiry->status == 'new')
                    <span class="status-pill" style="background:#DBEAFE;color:#1D4ED8;">New</span>
                @elseif($enquiry->status == 'follow_up')
                    <span class="status-pill warning">Follow Up</span>
                @elseif($enquiry->status == 'interested')
                    <span class="status-pill success">Interested</span>
                @elseif($enquiry->status == 'converted')
                    <span class="status-pill" style="background:#DCFCE7;color:#166534;">Converted</span>
                @elseif($enquiry->status == 'not_interested')
                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Not Interested</span>
                @else
                    <span class="status-pill" style="background:#F1F5F9;color:#475569;">Rejected</span>
                @endif
            </div>

            <div class="detail-section-pad-sm">
                <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <div class="stat-mini">
                        <p class="stat-mini-label">Enquiry ID</p>
                        <p class="stat-mini-value">#{{ $enquiry->id }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Source</p>
                        <p class="stat-mini-value-sm">{{ $enquiry->source ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Enquiry Date</p>
                        <p class="stat-mini-value-sm">
                            {{ optional($enquiry->enquiry_date)->format('d M Y') ?? '-' }}
                        </p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Next Follow-up</p>
                        <p class="stat-mini-value-sm">
                            {{ optional($enquiry->next_follow_up_date)->format('Y M d') ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('enquiry_edit')
                    <a href="{{ route('admin.enquiries.edit', $enquiry->id) }}" class="quick-link primary">
                        <i class="fas fa-pencil-alt"></i>
                        Edit Enquiry
                    </a>
                @endcan

                <a href="{{ route('admin.enquiries.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Enquiries
                </a>

                @can('enquiry_create')
                    <a href="{{ route('admin.enquiries.create') }}" class="quick-link">
                        <i class="fas fa-plus"></i>
                        Add New Enquiry
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

                <p class="detail-section-title">Enquiry Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Student Name</span>
                    <span class="detail-value">{{ $enquiry->student_name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">{{ $enquiry->phone }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Alternate Phone</span>
                    <span class="detail-value">{{ $enquiry->alternate_phone ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ $enquiry->email ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">{{ $enquiry->branch->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Course</span>
                    <span class="detail-value">{{ $enquiry->course->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Assigned To</span>
                    <span class="detail-value">{{ $enquiry->assignedTo->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Class</span>
                    <span class="detail-value">{{ $enquiry->class_name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">School</span>
                    <span class="detail-value">{{ $enquiry->school_name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Remarks</span>
                    <span class="detail-value">{{ $enquiry->remarks ?? '-' }}</span>
                </div>
            </div>
        </div>

        @can('enquiry_follow_up_create')
            <div class="detail-card mb-3">
                <div class="detail-section-head">
                    <div class="detail-section-icon">
                        <i class="fas fa-phone-volume"></i>
                    </div>

                    <p class="detail-section-title">Add Follow-up</p>
                </div>

                <div class="detail-section-body">
                    <form method="POST" action="{{ route('admin.enquiries.followUps.store', $enquiry->id) }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-4">
                                <div class="field-group">
                                    <label class="field-label" for="follow_up_date">
                                        Follow-up Date
                                    </label>

                                    <input type="date"
                                           name="follow_up_date"
                                           id="follow_up_date"
                                           value="{{ old('follow_up_date', date('Y-m-d')) }}"
                                           class="field-input {{ $errors->has('follow_up_date') ? 'error' : '' }}">

                                    @if($errors->has('follow_up_date'))
                                        <p class="field-error">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $errors->first('follow_up_date') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="field-group">
                                    <label class="field-label" for="follow_up_type">
                                        Follow-up Type
                                    </label>

                                    <select name="follow_up_type"
                                            id="follow_up_type"
                                            class="field-input {{ $errors->has('follow_up_type') ? 'error' : '' }}">
                                        <option value="">Please select</option>
                                        @foreach($followUpTypes as $key => $type)
                                            <option value="{{ $key }}" {{ old('follow_up_type') == $key ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @if($errors->has('follow_up_type'))
                                        <p class="field-error">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $errors->first('follow_up_type') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="field-group">
                                    <label class="field-label" for="next_follow_up_date">
                                        Next Follow-up Date
                                    </label>

                                    <input type="date"
                                           name="next_follow_up_date"
                                           id="next_follow_up_date"
                                           value="{{ old('next_follow_up_date') }}"
                                           class="field-input {{ $errors->has('next_follow_up_date') ? 'error' : '' }}">

                                    @if($errors->has('next_follow_up_date'))
                                        <p class="field-error">
                                            <i class="fas fa-exclamation-circle"></i>
                                            {{ $errors->first('next_follow_up_date') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="status">
                                Follow-up Status <span class="req">*</span>
                            </label>

                            <select name="status"
                                    id="status"
                                    required
                                    class="field-input {{ $errors->has('status') ? 'error' : '' }}">
                                <option value="follow_up" {{ old('status', 'follow_up') == 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                                <option value="interested" {{ old('status') == 'interested' ? 'selected' : '' }}>Interested</option>
                                <option value="not_interested" {{ old('status') == 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                                <option value="converted" {{ old('status') == 'converted' ? 'selected' : '' }}>Converted</option>
                                <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>

                            @if($errors->has('status'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('status') }}
                                </p>
                            @endif
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="response">
                                Response
                            </label>

                            <textarea name="response"
                                      id="response"
                                      rows="4"
                                      placeholder="Example: Parent asked for fee structure, demo class scheduled..."
                                      class="field-input {{ $errors->has('response') ? 'error' : '' }}">{{ old('response') }}</textarea>

                            @if($errors->has('response'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('response') }}
                                </p>
                            @endif
                        </div>

                        <div class="field-group">
                            <label class="field-label" for="remarks">
                                Remarks
                            </label>

                            <textarea name="remarks"
                                      id="remarks"
                                      rows="3"
                                      placeholder="Internal remarks"
                                      class="field-input {{ $errors->has('remarks') ? 'error' : '' }}">{{ old('remarks') }}</textarea>

                            @if($errors->has('remarks'))
                                <p class="field-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $errors->first('remarks') }}
                                </p>
                            @endif
                        </div>

                        <div class="form-actions" style="margin-top:10px;">
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-plus"></i>
                                Add Follow-up
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-history"></i>
                </div>

                <p class="detail-section-title">Follow-up History</p>
            </div>

            <div class="detail-section-body">
                @forelse($enquiry->followUps->sortByDesc('follow_up_date') as $followUp)
                    <div class="detail-row" style="align-items:flex-start;">
                        <span class="detail-label">
                            {{ optional($followUp->follow_up_date)->format('d M Y') ?? '-' }}
                        </span>

                        <div class="detail-value" style="width:100%;">
                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:6px;">
                                <span class="code-pill">
                                    {{ $followUp->follow_up_type ?? 'Follow-up' }}
                                </span>

                                <span class="status-pill warning">
                                    {{ ucwords(str_replace('_', ' ', $followUp->status)) }}
                                </span>

                                <span style="font-size:12px; color:#64748B;">
                                    By: {{ $followUp->followedBy->name ?? '-' }}
                                </span>
                            </div>

                            <p style="margin:0 0 6px; color:#334155;">
                                {{ $followUp->response ?? '-' }}
                            </p>

                            @if($followUp->next_follow_up_date)
                                <p style="margin:0 0 6px; font-size:12px; color:#475569;">
                                    <strong>Next Follow-up:</strong>
                                    {{ $followUp->next_follow_up_date->format('d M Y') }}
                                </p>
                            @endif

                            @if($followUp->remarks)
                                <p style="margin:0; font-size:12px; color:#64748B;">
                                    <strong>Remarks:</strong> {{ $followUp->remarks }}
                                </p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="detail-row">
                        <span class="detail-label">History</span>
                        <span class="detail-value">No follow-up added yet.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@endsection