@extends('layouts.admin')

@section('page-title', 'Student Fee Ledgers')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Student Fee Ledgers</h2>
        <p class="admin-page-subtitle">
            Actual fee obligation per student — net payable, concessions, paid and outstanding
        </p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Ledgers</p>
        <p class="stat-value">{{ $ledgers->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Net Payable</p>
        <p class="stat-value">₹{{ number_format($ledgers->sum('net_payable'), 0) }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Paid Till Date</p>
        <p class="stat-value">₹{{ number_format($ledgers->sum('paid_till_date'), 0) }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Total Outstanding</p>
        <p class="stat-value">₹{{ number_format($ledgers->sum('outstanding_amount'), 0) }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Student Fee Ledgers</p>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-StudentFeeLedger">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Fee Structure</th>
                    <th>Net Payable</th>
                    <th>Concession</th>
                    <th>Paid</th>
                    <th>Outstanding</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($ledgers as $ledger)
                    <tr data-entry-id="{{ $ledger->id }}">
                        <td></td>
                        <td><span class="id-text">#{{ $loop->iteration }}</span></td>
                        <td>{{ $ledger->student->user->name ?? '-' }}</td>
                        <td>{{ $ledger->feeStructure->title ?? '-' }} <span class="table-sub-text">v{{ $ledger->fee_structure_version }}</span></td>
                        <td>₹{{ number_format($ledger->net_payable, 2) }}</td>
                        <td>₹{{ number_format($ledger->concession_total, 2) }}</td>
                        <td><strong>₹{{ number_format($ledger->paid_till_date, 2) }}</strong></td>
                        <td>₹{{ number_format($ledger->outstanding_amount, 2) }}</td>
                        <td>
                            @if($ledger->status == 'active')
                                <span class="status-pill success">Active</span>
                            @elseif($ledger->status == 'closed')
                                <span class="status-pill">Closed</span>
                            @else
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-row">
                                @can('student_fee_ledger_show')
                                    <a href="{{ route('admin.student-fee-ledgers.show', $ledger->id) }}" class="btn-outline"><i class="fas fa-eye"></i> View</a>
                                @endcan
                            </div>
                        </td>
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
    initAdminDataTable('.datatable-StudentFeeLedger', {
        canDelete: false,
        searchPlaceholder: 'Search ledgers...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ ledgers'
    });
});
</script>
@endsection
