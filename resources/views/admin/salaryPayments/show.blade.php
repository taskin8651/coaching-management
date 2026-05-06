@extends('layouts.admin')

@section('page-title', 'Show Salary Payment')

@section('content')

<div class="admin-page-head">
    <div>
        <a href="{{ route('admin.salary-payments.index') }}" class="admin-back-link">
            ← {{ trans('global.back_to_list') }}
        </a>

        <h2 class="admin-page-title">Salary Payment Details</h2>

        <p class="admin-page-subtitle">
            Employee salary, payment status and salary slip details
        </p>
    </div>

    <div class="show-actions">
        @can('salary_payment_edit')
            <a href="{{ route('admin.salary-payments.edit', $salaryPayment->id) }}" class="btn-primary">
                <i class="fas fa-pencil-alt"></i>
                Edit Payment
            </a>
        @endcan

        @can('salary_payment_show')
            <a href="{{ route('admin.salary-payments.slip', $salaryPayment->id) }}"
               target="_blank"
               class="btn-outline">
                <i class="fas fa-file-invoice"></i>
                Salary Slip
            </a>
        @endcan

        @can('salary_payment_delete')
            <form action="{{ route('admin.salary-payments.destroy', $salaryPayment->id) }}"
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
                <div class="profile-avatar-lg" style="background:#10B981;">
                    <i class="fas fa-money-check-alt"></i>
                </div>

                <p class="profile-title">{{ $salaryPayment->employee_name }}</p>

                <p class="profile-subtitle">
                    {{ $salaryPayment->slip_no ?? 'Salary Slip' }}
                </p>

                @if($salaryPayment->payment_status == 'paid')
                    <span class="status-pill success">Paid</span>
                @elseif($salaryPayment->payment_status == 'partial')
                    <span class="status-pill warning">Partial</span>
                @elseif($salaryPayment->payment_status == 'cancelled')
                    <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                @else
                    <span class="status-pill" style="background:#F1F5F9;color:#475569;">Due</span>
                @endif
            </div>

            <div class="detail-section-pad-sm">
                <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <div class="stat-mini">
                        <p class="stat-mini-label">Net Salary</p>
                        <p class="stat-mini-value">₹{{ number_format($salaryPayment->net_salary, 0) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Paid</p>
                        <p class="stat-mini-value">₹{{ number_format($salaryPayment->paid_amount, 0) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Due</p>
                        <p class="stat-mini-value">₹{{ number_format($salaryPayment->due_amount, 0) }}</p>
                    </div>

                    <div class="stat-mini">
                        <p class="stat-mini-label">Month</p>
                        <p class="stat-mini-value-sm">{{ $salaryPayment->salary_month ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-user-tie"></i>
                </div>

                <p class="detail-section-title">Employee Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Employee</span>
                    <span class="detail-value">{{ $salaryPayment->employee_name }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Employee Type</span>
                    <span class="detail-value">{{ ucfirst($salaryPayment->employee_type) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Branch</span>
                    <span class="detail-value">{{ $salaryPayment->branch->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Salary Month</span>
                    <span class="detail-value">{{ $salaryPayment->salary_month ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Slip No</span>
                    <span class="detail-value code-pill">{{ $salaryPayment->slip_no ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="detail-card mb-3">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-calculator"></i>
                </div>

                <p class="detail-section-title">Salary Calculation</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Basic Salary</span>
                    <span class="detail-value">₹{{ number_format($salaryPayment->basic_salary, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Bonus</span>
                    <span class="detail-value">₹{{ number_format($salaryPayment->bonus, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Deduction</span>
                    <span class="detail-value">₹{{ number_format($salaryPayment->deduction, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Net Salary</span>
                    <span class="detail-value">₹{{ number_format($salaryPayment->net_salary, 2) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Paid Amount</span>
                    <span class="detail-value" style="color:#166534;">
                        ₹{{ number_format($salaryPayment->paid_amount, 2) }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Due Amount</span>
                    <span class="detail-value" style="color:#991B1B;">
                        ₹{{ number_format($salaryPayment->due_amount, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="detail-card">
            <div class="detail-section-head">
                <div class="detail-section-icon">
                    <i class="fas fa-credit-card"></i>
                </div>

                <p class="detail-section-title">Payment Details</p>
            </div>

            <div class="detail-section-body">
                <div class="detail-row">
                    <span class="detail-label">Payment Mode</span>
                    <span class="detail-value">{{ ucwords(str_replace('_', ' ', $salaryPayment->payment_mode)) }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Payment Date</span>
                    <span class="detail-value">
                        {{ $salaryPayment->payment_date ? \Carbon\Carbon::parse($salaryPayment->payment_date)->format('d M Y') : '-' }}
                    </span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Paid By</span>
                    <span class="detail-value">{{ $salaryPayment->paidBy->name ?? '-' }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Remarks</span>
                    <span class="detail-value">{{ $salaryPayment->remarks ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection