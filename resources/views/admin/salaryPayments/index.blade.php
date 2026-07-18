@extends('layouts.admin')

@section('page-title', 'Salary Payments')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Salary Payments</h2>
        <p class="admin-page-subtitle">
            Manage teacher, staff and manager salary payments
        </p>
    </div>

    @can('salary_payment_create')
        <a href="{{ route('admin.salary-payments.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Salary
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Paid</p>
        <p class="stat-value">₹{{ number_format($salaryPayments->sum('paid_amount'), 0) }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Total Due</p>
        <p class="stat-value">₹{{ number_format($salaryPayments->sum('due_amount'), 0) }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Paid Records</p>
        <p class="stat-value">{{ $salaryPayments->where('payment_status', 'paid')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Partial</p>
        <p class="stat-value">{{ $salaryPayments->where('payment_status', 'partial')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Salary Payments</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Salary slip, paid amount and due tracking
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-SalaryPayment">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Slip</th>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Branch</th>
                    <th>Month</th>
                    <th>Net</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($salaryPayments as $payment)
                    <tr data-entry-id="{{ $payment->id }}">
                        <td></td>

                        <td>
                            <span class="id-text">#{{ $loop->iteration }}</span>
                        </td>

                        <td>
                            <span class="code-pill">{{ $payment->slip_no ?? '-' }}</span>
                            <p class="table-sub-text">
                                {{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') : '-' }}
                            </p>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $name = $payment->employee_name;
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$payment->id % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $name }}</p>
                                    <p class="table-sub-text">
                                        {{ $payment->user->email ?? '-' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="role-tag">
                                {{ ucwords(str_replace('_', ' ', $payment->employee_type)) }}
                            </span>
                        </td>

                        <td>
                            {{ $payment->branch->name ?? '-' }}
                        </td>

                        <td>
                            {{ $payment->salary_month ?? '-' }}
                        </td>

                        <td>
                            ₹{{ number_format($payment->net_salary, 2) }}
                        </td>

                        <td>
                            <strong>₹{{ number_format($payment->paid_amount, 2) }}</strong>
                        </td>

                        <td>
                            ₹{{ number_format($payment->due_amount, 2) }}
                        </td>

                        <td>
                            @if($payment->payment_status == 'paid')
                                <span class="status-pill success">Paid</span>
                            @elseif($payment->payment_status == 'partial')
                                <span class="status-pill warning">Partial</span>
                            @elseif($payment->payment_status == 'cancelled')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Cancelled</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">Due</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('salary_payment_show')
                                    <a href="{{ route('admin.salary-payments.show', $payment->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>

                                    <a href="{{ route('admin.salary-payments.slip', $payment->id) }}"
                                       target="_blank"
                                       class="btn-outline">
                                        <i class="fas fa-file-invoice"></i>
                                        Slip
                                    </a>
                                @endcan

                                @can('salary_payment_edit')
                                    <a href="{{ route('admin.salary-payments.edit', $payment->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('salary_payment_delete')
                                    <form action="{{ route('admin.salary-payments.destroy', $payment->id) }}"
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
    initAdminDataTable('.datatable-SalaryPayment', {
        canDelete: @can('salary_payment_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.salary-payments.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search salary payments...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ salary payments'
    });
});
</script>
@endsection