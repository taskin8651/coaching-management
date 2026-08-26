@extends('layouts.admin')

@section('page-title', 'Refunds')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Refunds</h2>
        <p class="admin-page-subtitle">
            Money paid back out to a student — approval required before completion, immutable once completed
        </p>
    </div>

    @can('refund_create')
        <a href="{{ route('admin.refunds.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Request Refund
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total</p>
        <p class="stat-value">{{ $refunds->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Pending Approval</p>
        <p class="stat-value">{{ $refunds->where('approval_status', 'pending')->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Approved (awaiting completion)</p>
        <p class="stat-value">{{ $refunds->where('approval_status', 'approved')->where('status', '!=', 'completed')->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Completed Amount</p>
        <p class="stat-value">₹{{ number_format($refunds->where('status', 'completed')->sum('amount'), 0) }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Refunds</p>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Refund">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Amount</th>
                    <th>Mode</th>
                    <th>Approval</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($refunds as $refund)
                    <tr data-entry-id="{{ $refund->id }}">
                        <td></td>
                        <td><span class="id-text">#{{ $loop->iteration }}</span></td>
                        <td>
                            <p class="table-main-text">{{ $refund->student->user->name ?? '-' }}</p>
                            <p class="table-sub-text">{{ $refund->feePayment->receipt_no ?? 'Advance balance' }}</p>
                        </td>
                        <td>₹{{ number_format($refund->amount, 2) }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $refund->mode)) }}</td>
                        <td>
                            @if($refund->approval_status == 'approved')
                                <span class="status-pill success">Approved</span>
                            @elseif($refund->approval_status == 'rejected')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Rejected</span>
                            @else
                                <span class="status-pill warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            @if($refund->status == 'completed')
                                <span class="status-pill success">Completed</span>
                            @elseif($refund->status == 'cancelled')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                            @else
                                <span class="status-pill">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-row">
                                @can('refund_show')
                                    <a href="{{ route('admin.refunds.show', $refund->id) }}" class="btn-outline"><i class="fas fa-eye"></i> View</a>
                                @endcan

                                @can('refund_approve')
                                    @if($refund->approval_status == 'pending')
                                        <form action="{{ route('admin.refunds.approve', $refund->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn-outline" style="color:#166534;"><i class="fas fa-check"></i> Approve</button>
                                        </form>
                                        <form action="{{ route('admin.refunds.reject', $refund->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn-outline btn-outline-danger"><i class="fas fa-times"></i> Reject</button>
                                        </form>
                                    @endif
                                @endcan

                                @can('refund_complete')
                                    @if($refund->approval_status == 'approved' && $refund->status != 'completed')
                                        <form action="{{ route('admin.refunds.complete', $refund->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                            @csrf
                                            <button type="submit" class="btn-outline" style="color:#166534;"><i class="fas fa-money-bill-wave"></i> Mark Completed</button>
                                        </form>
                                    @endif
                                @endcan

                                @can('refund_delete')
                                    @if($refund->approval_status == 'pending' && $refund->status == 'pending')
                                        <form action="{{ route('admin.refunds.destroy', $refund->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn-outline btn-outline-danger"><i class="fas fa-trash-alt"></i> Delete</button>
                                        </form>
                                    @endif
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
    initAdminDataTable('.datatable-Refund', {
        canDelete: false,
        searchPlaceholder: 'Search refunds...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ refunds'
    });
});
</script>
@endsection
