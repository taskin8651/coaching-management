@extends('layouts.admin')

@section('page-title', 'Expenses')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Expenses</h2>
        <p class="admin-page-subtitle">
            Manage branch-wise daily expenses, bills and outgoing payments
        </p>
    </div>

    @can('expense_create')
        <a href="{{ route('admin.expenses.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Expense
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Expense</p>
        <p class="stat-value">₹{{ number_format($expenses->sum('amount'), 0) }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Paid</p>
        <p class="stat-value">₹{{ number_format($expenses->where('status', 'paid')->sum('amount'), 0) }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Pending</p>
        <p class="stat-value">{{ $expenses->where('status', 'pending')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Cancelled</p>
        <p class="stat-value">{{ $expenses->where('status', 'cancelled')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Expenses</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Track expense category, branch and payment mode
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Expense">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Expense</th>
                    <th>Category</th>
                    <th>Branch</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($expenses as $expense)
                    <tr data-entry-id="{{ $expense->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $expense->id }}</span>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$expense->id % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    <i class="fas fa-receipt"></i>
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $expense->title }}</p>
                                    <p class="table-sub-text">
                                        {{ $expense->vendor_name ?? 'Expense Record' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="role-tag">{{ $expense->category ?? '-' }}</span>
                        </td>

                        <td>
                            {{ $expense->branch->name ?? '-' }}
                        </td>

                        <td>
                            <strong>₹{{ number_format($expense->amount, 2) }}</strong>
                        </td>

                        <td>
                            {{ $expense->expense_date ? \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') : '-' }}
                        </td>

                        <td>
                            {{ ucwords(str_replace('_', ' ', $expense->payment_mode)) }}
                        </td>

                        <td>
                            @if($expense->status == 'paid')
                                <span class="status-pill success">Paid</span>
                            @elseif($expense->status == 'pending')
                                <span class="status-pill warning">Pending</span>
                            @else
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('expense_show')
                                    <a href="{{ route('admin.expenses.show', $expense->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('expense_edit')
                                    <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('expense_delete')
                                    <form action="{{ route('admin.expenses.destroy', $expense->id) }}"
                                          method="POST"
                                          style="display:inline;"
                                          onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @method('DELETE')
                                        @csrf

                                        <button type="submit" class="btn-outline btn-outline-danger">
                                            <i class="fas fa-trash-alt"></i>
                                            Delete
                                        </button>
                                    </form>
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
    initAdminDataTable('.datatable-Expense', {
        canDelete: @can('expense_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.expenses.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search expenses...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ expenses'
    });
});
</script>
@endsection