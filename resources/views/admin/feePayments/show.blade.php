@extends('layouts.admin')

@section('page-title', 'Show Fee Payment')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.fee-payments.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Fee Receipt</h2>

        <p class="admin-page-subtitle">
            Student payment receipt and due amount details
        </p>
    </div>

    <div class="show-actions">
        @if($feePayment->payment_status != 'cancelled')
            @can('fee_payment_edit')
                <a href="{{ route('admin.fee-payments.edit', $feePayment->id) }}" class="btn-primary">
                    <i class="fas fa-pencil-alt"></i>
                    Edit Payment
                </a>
            @endcan
        @endif

        <button type="button" onclick="window.print()" class="btn-outline">
            <i class="fas fa-print"></i>
            Print
        </button>

        @can('fee_payment_cancel')
            @if($feePayment->payment_status != 'cancelled')
                <button type="button" class="btn-outline btn-outline-danger" onclick="document.getElementById('cancelPaymentModal').style.display='flex'">
                    <i class="fas fa-ban"></i>
                    Cancel
                </button>
            @endif
        @endcan

        @can('fee_payment_delete')
            @if((float) $feePayment->paid_amount <= 0)
                <form action="{{ route('admin.fee-payments.destroy', $feePayment->id) }}"
                      method="POST"
                      onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                    @method('DELETE')
                    @csrf

                    <button type="submit" class="btn-danger">
                        <i class="fas fa-trash-alt"></i>
                        Delete
                    </button>
                </form>
            @endif
        @endcan
    </div>
</div>

@if($feePayment->payment_status == 'cancelled')
    <div class="form-info-box mb-3" style="border-color:#EF4444;background:#FEF2F2;">
        <p>
            <i class="fas fa-ban"></i>
            <strong>CANCELLED</strong> on {{ optional($feePayment->cancelled_at)->format('d M Y, H:i') }} by {{ $feePayment->cancelledBy->name ?? '-' }}.
            Reason: {{ $feePayment->cancel_reason ?? '-' }}
        </p>
    </div>
@endif

@can('fee_payment_cancel')
<div id="cancelPaymentModal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:1000;align-items:center;justify-content:center;">
    <div class="detail-card" style="max-width:420px;width:90%;">
        <div class="detail-section-head">
            <div class="detail-section-icon"><i class="fas fa-ban"></i></div>
            <p class="detail-section-title">Cancel Payment</p>
        </div>

        <form method="POST" action="{{ route('admin.fee-payments.cancel', $feePayment->id) }}" class="detail-section-body">
            @csrf
            <div class="field-group">
                <label class="field-label">Reason <span class="req">*</span></label>
                <textarea name="cancel_reason" rows="3" required minlength="5" class="field-input" placeholder="Why is this payment being cancelled?"></textarea>
            </div>

            <div class="form-actions" style="margin-top:12px;">
                <button type="submit" class="btn-danger"><i class="fas fa-ban"></i> Confirm Cancel</button>
                <button type="button" class="btn-ghost" onclick="document.getElementById('cancelPaymentModal').style.display='none'">Close</button>
            </div>
        </form>
    </div>
</div>
@endcan

<div class="show-grid">

    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background:#10B981;">
                    <i class="fas fa-rupee-sign"></i>
                </div>

                <p class="profile-title">{{ $feePayment->receipt_no ?? 'Receipt' }}</p>

                <p class="profile-subtitle">
                    {{ $feePayment->payment_date ? \Carbon\Carbon::parse($feePayment->payment_date)->format('d M Y') : '-' }}
                </p>

                @if($feePayment->payment_status == 'paid')
                    <span class="status-pill success">Paid</span>
                @elseif($feePayment->payment_status == 'partial')
                    <span class="status-pill warning">Partial</span>
                @elseif($feePayment->payment_status == 'cancelled')
                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                @else
                    <span class="status-pill" style="background:#F1F5F9;color:#475569;">Due</span>
                @endif
            </div>

            <div class="detail-section-pad-sm">
                <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <div class="stat-mini">
                        <p class="stat-mini-label">Payable</p>
                        <p class="stat-mini-value">₹{{ number_format($feePayment->payable_amount, 0) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Paid</p>
                        <p class="stat-mini-value">₹{{ number_format($feePayment->paid_amount, 0) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Due</p>
                        <p class="stat-mini-value">₹{{ number_format($feePayment->due_amount, 0) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Mode</p>
                        <p class="stat-mini-value-sm">
                            {{ ucwords(str_replace('_', ' ', $feePayment->payment_mode)) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('fee_payment_edit')
                    <a href="{{ route('admin.fee-payments.edit', $feePayment->id) }}" class="quick-link primary">
                        <i class="fas fa-pencil-alt"></i>
                        Edit Payment
                    </a>
                @endcan

                <a href="{{ route('admin.fee-payments.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Payments
                </a>

                @can('fee_payment_create')
                    <a href="{{ route('admin.fee-payments.create') }}" class="quick-link">
                        <i class="fas fa-plus"></i>
                        Add New Payment
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-receipt"></i>
                </div>

                <p class="detail-section-title">Receipt Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Receipt No</span>
                    <span class="detail-value code-pill">{{ $feePayment->receipt_no ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Payment Date</span>
                    <span class="detail-value">
                        {{ $feePayment->payment_date ? \Carbon\Carbon::parse($feePayment->payment_date)->format('d M Y') : '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Payment Mode</span>
                    <span class="detail-value">
                        {{ ucwords(str_replace('_', ' ', $feePayment->payment_mode)) }}
                        @if($feePayment->payment_mode == 'cheque' && $feePayment->cheque_number)
                            — #{{ $feePayment->cheque_number }} ({{ $feePayment->cheque_bank_name }})
                        @elseif($feePayment->payment_mode == 'upi' && $feePayment->upi_txn_ref)
                            — {{ $feePayment->upi_txn_ref }}
                        @elseif($feePayment->payment_mode == 'bank_transfer' && $feePayment->neft_rtgs_imps_utr)
                            — UTR {{ $feePayment->neft_rtgs_imps_utr }} ({{ $feePayment->neft_rtgs_imps_bank_name }})
                        @elseif($feePayment->payment_mode == 'card' && $feePayment->card_gateway_ref)
                            — {{ $feePayment->card_gateway_ref }}
                        @elseif($feePayment->payment_mode == 'other' && $feePayment->other_reference)
                            — {{ $feePayment->other_reference }}
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Fee Account</span>
                    <span class="detail-value">{{ $feePayment->feeAccount->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">GST</span>
                    <span class="detail-value">
                        @if($feePayment->gst_applicable)
                            {{ number_format($feePayment->gst_percent, 1) }}% (₹{{ number_format($feePayment->gst_amount, 2) }})
                        @else
                            Not applicable
                        @endif
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Collected By</span>
                    <span class="detail-value">{{ $feePayment->collectedBy->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">{{ ucfirst($feePayment->payment_status) }}</span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>

                <p class="detail-section-title">Student Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Student</span>
                    <span class="detail-value">{{ $feePayment->student?->user?->name ?? $feePayment->eventEnrollment?->participantName() ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Student Code</span>
                    <span class="detail-value">{{ $feePayment->student?->student_code ?? '-' }}</span>
                </div>

                @if($feePayment->eventEnrollment)
                    <div class="detail-row">
                        <span class="detail-label">Event</span>
                        <span class="detail-value">
                            @can('event_show')
                                <a href="{{ route('admin.events.show', $feePayment->eventEnrollment->event_id) }}">{{ $feePayment->eventEnrollment->event->name ?? '-' }}</a>
                            @else
                                {{ $feePayment->eventEnrollment->event->name ?? '-' }}
                            @endcan
                        </span>
                    </div>
                @endif

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">{{ $feePayment->branch->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Course</span>
                    <span class="detail-value">{{ $feePayment->course->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Batch</span>
                    <span class="detail-value">{{ $feePayment->batch->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-calculator"></i>
                </div>

                <p class="detail-section-title">Amount Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Total Fee</span>
                    <span class="detail-value">₹{{ number_format($feePayment->total_fee, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Discount</span>
                    <span class="detail-value">₹{{ number_format($feePayment->discount, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Payable Amount</span>
                    <span class="detail-value">₹{{ number_format($feePayment->payable_amount, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Paid Amount</span>
                    <span class="detail-value" style="color:#166534;">
                        ₹{{ number_format($feePayment->paid_amount, 2) }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Due Amount</span>
                    <span class="detail-value" style="color:#991B1B;">
                        ₹{{ number_format($feePayment->due_amount, 2) }}
                    </span>
                </div>
            </div>
        </div>

        @if($feePayment->allocations->isNotEmpty())
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-layer-group"></i></div>
                <p class="detail-section-title">Allocated Installments</p>
            </div>

            <div class="detail-section-body">
                @foreach($feePayment->allocations as $allocation)
                    <div class="detail-row">
                        <span class="detail-label">{{ $allocation->feeInstallment->title ?? '-' }}</span>
                        <span class="detail-value">₹{{ number_format($allocation->amount, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($feePayment->refunds->isNotEmpty())
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-undo-alt"></i></div>
                <p class="detail-section-title">Refunds Against This Payment</p>
            </div>

            <div class="detail-section-body">
                @foreach($feePayment->refunds as $refund)
                    <div class="detail-row">
                        <span class="detail-label">₹{{ number_format($refund->amount, 2) }} — {{ optional($refund->refund_date)->format('d M Y') }}</span>
                        @can('refund_show')
                            <a href="{{ route('admin.refunds.show', $refund->id) }}" class="detail-value">{{ ucfirst($refund->status) }}</a>
                        @else
                            <span class="detail-value">{{ ucfirst($refund->status) }}</span>
                        @endcan
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>

                <p class="detail-section-title">Remarks</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Remarks</span>
                    <span class="detail-value">{{ $feePayment->remarks ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection