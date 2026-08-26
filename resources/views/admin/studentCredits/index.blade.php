@extends('layouts.admin')

@section('page-title', 'Student Credits')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Student Advance / Credit History</h2>
        <p class="admin-page-subtitle">
            Overpayments held as reusable credit, and credit applied against installments or paid out as refunds
        </p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Credit Issued</p>
        <p class="stat-value">₹{{ number_format($transactions->where('type', 'credit')->sum('amount'), 0) }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Total Credit Consumed</p>
        <p class="stat-value">₹{{ number_format($transactions->where('type', 'debit')->sum('amount'), 0) }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Net Outstanding Credit</p>
        <p class="stat-value">₹{{ number_format($transactions->where('type', 'credit')->sum('amount') - $transactions->where('type', 'debit')->sum('amount'), 0) }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Credit Transactions</p>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-StudentCredit">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Type</th>
                    <th>Source</th>
                    <th>Amount</th>
                    <th>Installment</th>
                    <th>Remarks</th>
                </tr>
            </thead>

            <tbody>
                @foreach($transactions as $transaction)
                    <tr data-entry-id="{{ $transaction->id }}">
                        <td></td>
                        <td><span class="id-text">#{{ $loop->iteration }}</span></td>
                        <td>{{ $transaction->student->user->name ?? '-' }}</td>
                        <td>
                            @if($transaction->type == 'credit')
                                <span class="status-pill success">Credit</span>
                            @else
                                <span class="status-pill">Debit</span>
                            @endif
                        </td>
                        <td>{{ ucwords(str_replace('_', ' ', $transaction->source)) }}</td>
                        <td>₹{{ number_format($transaction->amount, 2) }}</td>
                        <td>{{ $transaction->feeInstallment->title ?? '-' }}</td>
                        <td>{{ $transaction->remarks ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
$(function () {
    initAdminDataTable('.datatable-StudentCredit', {
        canDelete: false,
        searchPlaceholder: 'Search credit transactions...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ transactions'
    });
});
</script>
@endsection
