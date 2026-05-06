@extends('layouts.admin')

@section('page-title', 'Show Expense')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.expenses.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Expense Details</h2>

        <p class="admin-page-subtitle">
            Full details for branch expense record
        </p>
    </div>

    <div class="show-actions">
        @can('expense_edit')
            <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Expense
            </a>
        @endcan

        @can('expense_delete')
            <form action="{{ route('admin.expenses.destroy', $expense->id) }}"
                  method="POST"
                  onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                @method('DELETE')
                @csrf

                <button type="submit" class="btn-danger">
                    <i class="fas fa-trash-alt"></i>
                    Delete
                </button>
            </form>
        @endcan
    </div>
</div>

<div class="show-grid">

    <div>
        <div class="detail-card mb-3">
            <div class="profile-hero">
                <div class="profile-avatar-lg" style="background:#EF4444;">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>

                <p class="profile-title">{{ $expense->title }}</p>

                <p class="profile-subtitle">
                    ₹{{ number_format($expense->amount, 2) }}
                </p>

                @if($expense->status == 'paid')
                    <span class="status-pill success">Paid</span>
                @elseif($expense->status == 'pending')
                    <span class="status-pill warning">Pending</span>
                @else
                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                @endif
            </div>

            <div class="detail-section-pad-sm">
                <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <div class="stat-mini">
                        <p class="stat-mini-label">Expense ID</p>
                        <p class="stat-mini-value">#{{ $expense->id }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Amount</p>
                        <p class="stat-mini-value">₹{{ number_format($expense->amount, 0) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Category</p>
                        <p class="stat-mini-value-sm">{{ $expense->category ?? '-' }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Date</p>
                        <p class="stat-mini-value-sm">
                            {{ $expense->expense_date ? \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') : '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-card detail-card-pad">
            <p class="quick-title">Quick Actions</p>

            <div class="quick-list">
                @can('expense_edit')
                    <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="quick-link primary">
                        <i class="fas fa-pencil-alt"></i>
                        Edit Expense
                    </a>
                @endcan

                <a href="{{ route('admin.expenses.index') }}" class="quick-link">
                    <i class="fas fa-list"></i>
                    All Expenses
                </a>

                @can('expense_create')
                    <a href="{{ route('admin.expenses.create') }}" class="quick-link">
                        <i class="fas fa-plus"></i>
                        Add New Expense
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-info-circle"></i>
                </div>

                <p class="detail-section-title">Expense Information</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Title</span>
                    <span class="detail-value">{{ $expense->title }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Category</span>
                    <span class="detail-value">{{ $expense->category ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">{{ $expense->branch->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Amount</span>
                    <span class="detail-value">₹{{ number_format($expense->amount, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Expense Date</span>
                    <span class="detail-value">
                        {{ $expense->expense_date ? \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') : '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Payment Mode</span>
                    <span class="detail-value">
                        {{ ucwords(str_replace('_', ' ', $expense->payment_mode)) }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Paid By</span>
                    <span class="detail-value">{{ $expense->paidBy->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">{{ ucfirst($expense->status) }}</span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-file-alt"></i>
                </div>

                <p class="detail-section-title">Bill Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Vendor / Person</span>
                    <span class="detail-value">{{ $expense->vendor_name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Bill No</span>
                    <span class="detail-value">{{ $expense->bill_no ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Remarks</span>
                    <span class="detail-value">{{ $expense->remarks ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection