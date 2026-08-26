@extends('layouts.admin')

@section('page-title', 'Show Refund')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.refunds.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">Refund #{{ $refund->id }}</h2>
        <p class="admin-page-subtitle">{{ $refund->student->user->name ?? '-' }}</p>
    </div>

    <div class="show-actions">
        @can('refund_approve')
            @if($refund->approval_status == 'pending')
                <form action="{{ route('admin.refunds.approve', $refund->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Approve</button>
                </form>
                <form action="{{ route('admin.refunds.reject', $refund->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-danger"><i class="fas fa-times"></i> Reject</button>
                </form>
            @endif
        @endcan

        @can('refund_complete')
            @if($refund->approval_status == 'approved' && $refund->status != 'completed')
                <form action="{{ route('admin.refunds.complete', $refund->id) }}" method="POST" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                    @csrf
                    <button type="submit" class="btn-primary"><i class="fas fa-money-bill-wave"></i> Mark Completed</button>
                </form>
            @endif
        @endcan

        @can('refund_edit')
            @if($refund->approval_status == 'pending')
                <a href="{{ route('admin.refunds.edit', $refund->id) }}" class="btn-outline"><i class="fas fa-pencil-alt"></i> Edit</a>
            @endif
        @endcan
    </div>
</div>

<div class="show-grid">
    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background:#EF4444;">
                    <i class="fas fa-undo-alt"></i>
                </div>
                <p class="profile-title">₹{{ number_format($refund->amount, 2) }}</p>
                <p class="profile-subtitle">{{ $refund->student->user->name ?? '-' }}</p>

                @if($refund->status == 'completed')
                    <span class="status-pill success">Completed</span>
                @elseif($refund->approval_status == 'rejected')
                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Rejected</span>
                @elseif($refund->approval_status == 'approved')
                    <span class="status-pill success">Approved — awaiting completion</span>
                @else
                    <span class="status-pill warning">Pending Approval</span>
                @endif
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>
            <div class="quick-list">
                <a href="{{ route('admin.refunds.index') }}" class="quick-link"><i class="fas fa-list"></i> All Refunds</a>
                @if($refund->student_fee_ledger_id)
                    <a href="{{ route('admin.student-fee-ledgers.show', $refund->student_fee_ledger_id) }}" class="quick-link"><i class="fas fa-file-invoice-dollar"></i> View Fee Ledger</a>
                @endif
                @if($refund->fee_payment_id)
                    <a href="{{ route('admin.fee-payments.show', $refund->fee_payment_id) }}" class="quick-link"><i class="fas fa-receipt"></i> View Originating Payment</a>
                @endif
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-info-circle"></i></div>
                <p class="detail-section-title">Refund Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Student</span><span class="detail-value">{{ $refund->student->user->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Originating Payment</span><span class="detail-value">{{ $refund->feePayment->receipt_no ?? 'Advance/credit balance' }}</span></div>
                <div class="detail-row"><span class="detail-label">Installment</span><span class="detail-value">{{ $refund->feeInstallment->title ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Fee Account</span><span class="detail-value">{{ $refund->feeAccount->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Amount</span><span class="detail-value">₹{{ number_format($refund->amount, 2) }}</span></div>
                <div class="detail-row"><span class="detail-label">Mode</span><span class="detail-value">{{ ucwords(str_replace('_', ' ', $refund->mode)) }}{{ $refund->reference_no ? ' — ' . $refund->reference_no : '' }}</span></div>
                <div class="detail-row"><span class="detail-label">Refund Date</span><span class="detail-value">{{ optional($refund->refund_date)->format('d M Y') }}</span></div>
                <div class="detail-row"><span class="detail-label">Reason</span><span class="detail-value">{{ $refund->reason }}</span></div>
                <div class="detail-row"><span class="detail-label">Remarks</span><span class="detail-value">{{ $refund->remarks ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Requested By</span><span class="detail-value">{{ $refund->createdBy->name ?? '-' }}</span></div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-history"></i></div>
                <p class="detail-section-title">Approval & Completion Trail</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Approval Status</span><span class="detail-value">{{ ucfirst($refund->approval_status) }}</span></div>
                <div class="detail-row"><span class="detail-label">Approved/Rejected By</span><span class="detail-value">{{ $refund->approvedBy->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Approval Date</span><span class="detail-value">{{ optional($refund->approval_date)->format('d M Y') ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">{{ ucfirst($refund->status) }}</span></div>
                <div class="detail-row"><span class="detail-label">Completed By</span><span class="detail-value">{{ $refund->completedBy->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Completed At</span><span class="detail-value">{{ optional($refund->completed_at)->format('d M Y, H:i') ?? '-' }}</span></div>
            </div>
        </div>
    </div>
</div>

@endsection
