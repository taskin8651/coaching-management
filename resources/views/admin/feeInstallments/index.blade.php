@extends('layouts.admin')

@section('page-title', 'Fee Installments')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Fee Installments</h2>
        <p class="admin-page-subtitle">
            Student-wise installment schedule, due amount and reminders
        </p>
    </div>

    @can('fee_installment_create')
        <a href="{{ route('admin.fee-installments.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Installment
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Installments</p>
        <p class="stat-value">{{ $installments->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Paid</p>
        <p class="stat-value">{{ $installments->where('status', 'paid')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Partial</p>
        <p class="stat-value">{{ $installments->where('status', 'partial')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Total Due</p>
        <p class="stat-value">₹{{ number_format($installments->sum('due_amount'), 0) }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Fee Installments</p>

        <span class="page-card-note">
            <i class="fas fa-bell"></i>
            Send reminders for pending or overdue installments
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Installments">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Title</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th style="text-align:right;">Reminder</th>
                </tr>
            </thead>

            <tbody>
                @foreach($installments as $item)
                    <tr>
                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $name = $item->student->user->name ?? 'Student';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $name }}</p>
                                    <p class="table-sub-text">{{ $item->student->student_code ?? 'Student' }}</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <p class="table-main-text">{{ $item->title }}</p>
                            <p class="table-sub-text">Installment Schedule</p>
                        </td>

                        <td>
                            ₹{{ number_format($item->amount, 2) }}
                        </td>

                        <td>
                            <strong>₹{{ number_format($item->paid_amount, 2) }}</strong>
                        </td>

                        <td>
                            @if((float) $item->due_amount > 0)
                                <strong style="color:#B91C1C;">₹{{ number_format($item->due_amount, 2) }}</strong>
                            @else
                                <strong style="color:#047857;">₹{{ number_format($item->due_amount, 2) }}</strong>
                            @endif
                        </td>

                        <td>
                            {{ $item->due_date ? \Carbon\Carbon::parse($item->due_date)->format('d M Y') : '-' }}
                        </td>

                        <td>
                            @if($item->status == 'paid')
                                <span class="status-pill success">Paid</span>
                            @elseif($item->status == 'partial')
                                <span class="status-pill warning">Partial</span>
                            @elseif($item->status == 'overdue')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Overdue</span>
                            @elseif($item->status == 'pending' || $item->status == 'due')
                                <span class="status-pill" style="background:#FEF3C7;color:#92400E;">
                                    {{ ucfirst($item->status) }}
                                </span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($item->status ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('fee_installment_remind')
                                    <form method="POST"
                                          action="{{ route('admin.fee-installments.remind', $item->id) }}"
                                          style="display:inline;">
                                        @csrf

                                        <button type="submit" class="btn-outline">
                                            <i class="fas fa-paper-plane"></i>
                                            Send
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size:12px;color:#94A3B8;">—</span>
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
    initAdminDataTable('.datatable-Installments', {
        searchPlaceholder: 'Search installments...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ installments'
    });
});
</script>
@endsection