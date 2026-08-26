@extends('layouts.admin')

@section('page-title', 'Show Fee Account')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-accounts.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">{{ $feeAccount->name }}</h2>
        <p class="admin-page-subtitle">Fee account details</p>
    </div>

    <div class="show-actions">
        @can('fee_account_edit')
            <a href="{{ route('admin.fee-accounts.edit', $feeAccount->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i> Edit Fee Account
            </a>
        @endcan
    </div>
</div>

<div class="show-grid">
    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                @if($feeAccount->qr_code_url)
                    <img src="{{ $feeAccount->qr_code_url }}" alt="QR Code" style="max-width:140px;border-radius:16px;margin-bottom:12px;">
                @else
                    <div class="profile-avatar-lg" style="background:#0EA5E9;">
                        <i class="fas fa-university"></i>
                    </div>
                @endif

                <p class="profile-title">{{ $feeAccount->name }}</p>
                <p class="profile-subtitle">{{ ucfirst($feeAccount->type) }} • {{ $feeAccount->code }}</p>

                @if($feeAccount->status == 'active')
                    <span class="status-pill success">Active</span>
                @else
                    <span class="status-pill warning">Inactive</span>
                @endif
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>
            <div class="quick-list">
                <a href="{{ route('admin.fee-accounts.index') }}" class="quick-link"><i class="fas fa-list"></i> All Fee Accounts</a>
                @can('fee_account_create')
                    <a href="{{ route('admin.fee-accounts.create') }}" class="quick-link"><i class="fas fa-plus"></i> Add New Fee Account</a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-info-circle"></i></div>
                <p class="detail-section-title">Account Information</p>
            </div>
            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Code</span><span class="detail-value code-pill">{{ $feeAccount->code }}</span></div>
                <div class="detail-row"><span class="detail-label">Branch</span><span class="detail-value">{{ $feeAccount->branch->name ?? 'Shared / All Branches' }}</span></div>
                <div class="detail-row"><span class="detail-label">Type</span><span class="detail-value">{{ ucfirst($feeAccount->type) }}</span></div>
                <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">{{ ucfirst($feeAccount->status) }}</span></div>
            </div>
        </div>

        @if($feeAccount->type == 'bank')
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-money-check-alt"></i></div>
                <p class="detail-section-title">Banking Details</p>
            </div>
            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Bank Name</span><span class="detail-value">{{ $feeAccount->bank_name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Account Number</span><span class="detail-value">{{ $feeAccount->account_number ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">IFSC Code</span><span class="detail-value">{{ $feeAccount->ifsc_code ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">UPI ID</span><span class="detail-value">{{ $feeAccount->upi_id ?? '-' }}</span></div>
            </div>
        </div>
        @endif

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-file-invoice"></i></div>
                <p class="detail-section-title">GST & Receipt Details</p>
            </div>
            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">GST Applicable</span><span class="detail-value">{{ $feeAccount->gst_applicable ? 'Yes' : 'No' }}</span></div>
                <div class="detail-row"><span class="detail-label">GST Number</span><span class="detail-value">{{ $feeAccount->gst_number ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Receipt Address</span><span class="detail-value">{{ $feeAccount->receipt_address ?? '-' }}</span></div>
            </div>
        </div>
    </div>
</div>

@endsection
