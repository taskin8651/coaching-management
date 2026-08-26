@extends('layouts.admin')

@section('page-title', 'Student Fee Ledger')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.student-fee-ledgers.index') }}" class="admin-back-link">← {{ trans('global.back_to_list') }}</a>
        <h2 class="admin-page-title">{{ $studentFeeLedger->student->user->name ?? 'Student' }}</h2>
        <p class="admin-page-subtitle">{{ $studentFeeLedger->feeStructure->title ?? '-' }} — v{{ $studentFeeLedger->fee_structure_version }}</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Net Payable</p>
        <p class="stat-value">₹{{ number_format($studentFeeLedger->net_payable, 0) }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Concession</p>
        <p class="stat-value">₹{{ number_format($studentFeeLedger->concession_total, 0) }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Paid Till Date</p>
        <p class="stat-value">₹{{ number_format($studentFeeLedger->paid_till_date, 0) }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Outstanding</p>
        <p class="stat-value">₹{{ number_format($studentFeeLedger->outstanding_amount, 0) }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Advance / Credit Balance</p>
        <p class="stat-value">₹{{ number_format($studentFeeLedger->advance_balance, 0) }}</p>
    </div>
</div>

<div class="show-grid">
    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-info-circle"></i></div>
                <p class="detail-section-title">Ledger Information</p>
            </div>
            <div class="detail-section-body">
                <div class="detail-row"><span class="detail-label">Student</span><span class="detail-value">{{ $studentFeeLedger->student->user->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Student Code</span><span class="detail-value">{{ $studentFeeLedger->student->student_code ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Fee Structure</span><span class="detail-value">{{ $studentFeeLedger->feeStructure->title ?? '-' }} (v{{ $studentFeeLedger->fee_structure_version }})</span></div>
                <div class="detail-row"><span class="detail-label">Assigned By</span><span class="detail-value">{{ $studentFeeLedger->assignedBy->name ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Assigned At</span><span class="detail-value">{{ optional($studentFeeLedger->assigned_at)->format('d M Y') ?? '-' }}</span></div>
                <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">{{ ucfirst($studentFeeLedger->status) }}</span></div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-percent"></i></div>
                <p class="detail-section-title">Concessions</p>
            </div>
            <div class="detail-section-body">
                @forelse($studentFeeLedger->concessions as $concession)
                    <div class="detail-row">
                        <span class="detail-label">{{ $concession->type }}</span>
                        <span class="detail-value">
                            {{ $concession->amount_type == 'percentage' ? number_format($concession->percentage, 1) . '%' : '₹' . number_format($concession->amount, 2) }}
                            ({{ ucfirst($concession->approval_status) }})
                        </span>
                    </div>
                @empty
                    <p class="table-sub-text">No concessions recorded.</p>
                @endforelse
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-coins"></i></div>
                <p class="detail-section-title">Advance / Credit Balance</p>
            </div>
            <div class="detail-section-body">
                @forelse($studentFeeLedger->credits as $credit)
                    <div class="detail-row">
                        <span class="detail-label">{{ ucwords(str_replace('_', ' ', $credit->source)) }} ({{ ucfirst($credit->type) }})</span>
                        <span class="detail-value" style="color: {{ $credit->type == 'credit' ? '#166534' : '#991B1B' }};">
                            {{ $credit->type == 'credit' ? '+' : '-' }}₹{{ number_format($credit->amount, 2) }}
                        </span>
                    </div>
                @empty
                    <p class="table-sub-text">No credit transactions yet.</p>
                @endforelse

                @can('credit_apply')
                    @if((float) $studentFeeLedger->advance_balance > 0)
                        <form method="POST" action="{{ route('admin.student-fee-ledgers.applyCredit', $studentFeeLedger->id) }}" class="mt-3">
                            @csrf
                            <div class="field-group">
                                <label class="field-label">Apply Credit To Installment</label>
                                <select name="fee_installment_id" required class="field-input">
                                    @foreach($studentFeeLedger->installments->where('due_amount', '>', 0) as $installment)
                                        <option value="{{ $installment->id }}">{{ $installment->title }} — Due ₹{{ number_format($installment->due_amount, 0) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Amount (max ₹{{ number_format($studentFeeLedger->advance_balance, 2) }})</label>
                                <input type="number" step="0.01" min="0.01" max="{{ $studentFeeLedger->advance_balance }}" name="amount" required class="field-input">
                            </div>
                            <button type="submit" class="btn-mini-primary"><i class="fas fa-check"></i> Apply Credit</button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-undo-alt"></i></div>
                <p class="detail-section-title">Refunds</p>
            </div>
            <div class="detail-section-body">
                @forelse($studentFeeLedger->refunds as $refund)
                    <div class="detail-row">
                        <span class="detail-label">₹{{ number_format($refund->amount, 2) }} — {{ ucfirst($refund->refund_date ? \Illuminate\Support\Carbon::parse($refund->refund_date)->format('d M Y') : '') }}</span>
                        <span class="detail-value">{{ ucfirst($refund->status) }}</span>
                    </div>
                @empty
                    <p class="table-sub-text">No refunds recorded.</p>
                @endforelse

                @can('refund_create')
                    <a href="{{ route('admin.refunds.create') }}" class="quick-link primary mt-2">
                        <i class="fas fa-plus"></i> Request Refund
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-rupee-sign"></i></div>
                <p class="detail-section-title">Fee Structure Line Items</p>
            </div>

            <div class="page-card-table">
                <table class="min-w-full">
                    <thead><tr><th>Fee Head</th><th>Amount</th><th>Line Total</th></tr></thead>
                    <tbody>
                        @foreach($studentFeeLedger->feeStructure->items as $item)
                            <tr>
                                <td>{{ $item->feeHead->name ?? '-' }}</td>
                                <td>₹{{ number_format($item->amount, 2) }}</td>
                                <td>₹{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon"><i class="fas fa-calendar-alt"></i></div>
                <p class="detail-section-title">Installments & Payments</p>
            </div>

            <div class="page-card-table">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th>Installment</th>
                            <th>Amount</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Payments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($studentFeeLedger->installments as $installment)
                            <tr>
                                <td>{{ $installment->title }}</td>
                                <td>₹{{ number_format($installment->amount, 2) }}</td>
                                <td>₹{{ number_format($installment->paid_amount, 2) }}</td>
                                <td>₹{{ number_format($installment->due_amount, 2) }}</td>
                                <td>{{ optional($installment->due_date)->format('d M Y') ?? '-' }}</td>
                                <td>
                                    @if($installment->display_status == 'paid')
                                        <span class="status-pill success">Paid</span>
                                    @elseif($installment->display_status == 'overdue')
                                        <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Overdue</span>
                                    @elseif($installment->display_status == 'partial')
                                        <span class="status-pill warning">Partial</span>
                                    @else
                                        <span class="status-pill">{{ ucfirst($installment->display_status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @can('fee_payment_show')
                                        @foreach($installment->payments as $payment)
                                            <a href="{{ route('admin.fee-payments.show', $payment->id) }}" class="code-pill" style="display:inline-block;margin:2px;">
                                                {{ $payment->receipt_no }}
                                            </a>
                                        @endforeach
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7">No installments generated yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
