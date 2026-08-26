@extends('layouts.admin')

@section('page-title', 'Show Concession')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.concessions.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Concession Details</h2>
        <p class="admin-page-subtitle">{{ $concession->student->user->name ?? '-' }}</p>
    </div>

    <div class="show-actions">
        @can('concession_approve')
            @if($concession->approval_status == 'pending')
                <form action="{{ route('admin.concessions.approve', $concession->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Approve</button>
                </form>
                <form action="{{ route('admin.concessions.reject', $concession->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-danger"><i class="fas fa-times"></i> Reject</button>
                </form>
            @endif
        @endcan

        @can('concession_edit')
            @if($concession->approval_status == 'pending')
                <a href="{{ route('admin.concessions.edit', $concession->id) }}" class="btn-outline"><i class="fas fa-pencil-alt"></i> Edit</a>
            @endif
        @endcan
    </div>
</div>

<div class="show-grid">
    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background:#8B5CF6;">
                    <i class="fas fa-percent"></i>
                </div>
                <p class="profile-title">{{ $concession->type }}</p>
                <p class="profile-subtitle">{{ $concession->student->user->name ?? '-' }}</p>

                @if($concession->approval_status == 'approved')
                    <span class="status-pill success">Approved</span>
                @elseif($concession->approval_status == 'rejected')
                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Rejected</span>
                @else
                    <span class="status-pill warning">Pending</span>
                @endif
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>
            <div class="quick-list">
                <a href="{{ route('admin.concessions.index') }}" class="quick-link"><i class="fas fa-list"></i> All Concessions</a>
                @if($concession->student_fee_ledger_id)
                    <a href="{{ route('admin.student-fee-ledgers.show', $concession->student_fee_ledger_id) }}" class="quick-link"><i class="fas fa-file-invoice-dollar"></i> View Fee Ledger</a>
                @endif
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-info-circle"></i></div>
                <p class="detail-section-title">Concession Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Student</span><span class="detail-value">{{ $concession->student->user->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Fee Structure</span><span class="detail-value">{{ $concession->ledger->feeStructure->title ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Type</span><span class="detail-value">{{ $concession->type }}</span></div>
                <div class="detail-row">
                    <span class="detail-label">Value</span>
                    <span class="detail-value">{{ $concession->amount_type == 'percentage' ? number_format($concession->percentage, 2) . '%' : '₹' . number_format($concession->amount, 2) }}</span>
                </div>
                <div class="detail-row"><span class="detail-label">Reason</span><span class="detail-value">{{ $concession->reason ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Remarks</span><span class="detail-value">{{ $concession->remarks ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Requested By</span><span class="detail-value">{{ $concession->createdBy->name ?? '-' }}</span></div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-user-check"></i></div>
                <p class="detail-section-title">Approval</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">{{ ucfirst($concession->approval_status) }}</span></div>
                <div class="detail-row"><span class="detail-label">Approved/Rejected By</span><span class="detail-value">{{ $concession->approvedBy->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Date</span><span class="detail-value">{{ optional($concession->approval_date)->format('d M Y') ?? '-' }}</span></div>
            </div>
        </div>
    </div>
</div>

@endsection
