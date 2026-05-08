@extends('layouts.admin')

@section('page-title', 'Show Admission')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.admissions.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">{{ $admission->admission_no }}</h2>
        <p class="admin-page-subtitle">
            Admission profile, guardian details and fee snapshot
        </p>
    </div>

    @can('admission_edit')
        <a href="{{ route('admin.admissions.edit', $admission->id) }}" class="btn-primary">
            <i class="fas fa-pencil-alt"></i>
            Edit Admission
        </a>
    @endcan
</div>

<div class="show-grid">

    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background:#4F46E5;">
                    {{ strtoupper(substr($admission->student->user->name ?? 'A', 0, 1)) }}
                </div>

                <p class="profile-title">{{ $admission->student->user->name ?? '-' }}</p>
                <p class="profile-subtitle">{{ $admission->admission_no }}</p>

                @if($admission->status == 'confirmed')
                    <span class="status-pill success">Confirmed</span>
                @elseif($admission->status == 'pending')
                    <span class="status-pill warning">Pending</span>
                @else
                    <span class="status-pill">{{ ucfirst($admission->status) }}</span>
                @endif
            </div>

            <div class="detail-section-pad-sm">
                <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <div class="stat-mini">
                        <p class="stat-mini-label">Payable</p>
                        <p class="stat-mini-value-sm">₹{{ number_format($admission->payable_amount, 2) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Admission Date</p>
                        <p class="stat-mini-value-sm">
                            {{ $admission->admission_date ? \Carbon\Carbon::parse($admission->admission_date)->format('d M Y') : '-' }}
                        </p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Branch</p>
                        <p class="stat-mini-value-sm">{{ $admission->branch->name ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Course</p>
                        <p class="stat-mini-value-sm">{{ $admission->course->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('admission_edit')
                    <a href="{{ route('admin.admissions.edit', $admission->id) }}" class="quick-link primary">
                        <i class="fas fa-pencil-alt"></i>
                        Edit Admission
                    </a>
                @endcan

                <a href="{{ route('admin.admissions.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Admissions
                </a>
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-info-circle"></i>
                </div>

                <p class="detail-section-title">Admission Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Admission No</span>
                    <span class="detail-value">{{ $admission->admission_no }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Student</span>
                    <span class="detail-value">{{ $admission->student->user->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">{{ $admission->branch->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Course</span>
                    <span class="detail-value">{{ $admission->course->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Batch</span>
                    <span class="detail-value">{{ $admission->batch->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Source</span>
                    <span class="detail-value">{{ $admission->admission_source ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Created By</span>
                    <span class="detail-value">{{ $admission->createdBy->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-users"></i>
                </div>

                <p class="detail-section-title">Guardian Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Father</span>
                    <span class="detail-value">{{ $admission->father_name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Mother</span>
                    <span class="detail-value">{{ $admission->mother_name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Guardian</span>
                    <span class="detail-value">{{ $admission->guardian_name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Relation</span>
                    <span class="detail-value">{{ $admission->guardian_relation ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">{{ $admission->guardian_phone ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Parent Email</span>
                    <span class="detail-value">{{ $admission->parent_email ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Emergency</span>
                    <span class="detail-value">{{ $admission->emergency_contact ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-rupee-sign"></i>
                </div>

                <p class="detail-section-title">Fee Snapshot</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Course Fee</span>
                    <span class="detail-value">₹{{ number_format($admission->course_fee, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Admission Fee</span>
                    <span class="detail-value">₹{{ number_format($admission->admission_fee, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Discount</span>
                    <span class="detail-value">₹{{ number_format($admission->discount, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Payable Amount</span>
                    <span class="detail-value">
                        <strong>₹{{ number_format($admission->payable_amount, 2) }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-file"></i>
                </div>

                <p class="detail-section-title">Documents & Remarks</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Previous School</span>
                    <span class="detail-value">{{ $admission->previous_school ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Previous Class</span>
                    <span class="detail-value">{{ $admission->previous_class ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Qualification</span>
                    <span class="detail-value">{{ $admission->qualification ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Remarks</span>
                    <span class="detail-value">{{ $admission->remarks ?? '-' }}</span>
                </div>

                @if($admission->documents && count($admission->documents))
                    @foreach($admission->documents as $document)
                        <div class="detail-row">
                            <span class="detail-label">Document</span>
                            <span class="detail-value">
                                <a href="{{ $document['url'] }}" target="_blank">
                                    <i class="fas fa-download"></i>
                                    {{ $document['name'] }}
                                </a>
                            </span>
                        </div>
                    @endforeach
                @else
                    <div class="detail-row">
                        <span class="detail-label">Documents</span>
                        <span class="detail-value">No documents uploaded.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@endsection