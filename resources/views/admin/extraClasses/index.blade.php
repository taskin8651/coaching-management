@extends('layouts.admin')

@section('page-title', 'Extra Classes')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Extra Classes</h2>
        <p class="admin-page-subtitle">
            Approved extra classes, salary minutes and approval workflow
        </p>
    </div>

    @can('extra_class_create')
        <a href="{{ route('admin.extra-classes.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Extra Class
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Extra Classes</p>
        <p class="stat-value">{{ $extraClasses->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Approved</p>
        <p class="stat-value">{{ $extraClasses->where('approval_status', 'approved')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Pending</p>
        <p class="stat-value">{{ $extraClasses->where('approval_status', 'pending')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Approved Amount</p>
        <p class="stat-value">
            ₹{{ number_format($extraClasses->where('approval_status', 'approved')->sum('salary_amount'), 0) }}
        </p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Extra Classes</p>

        <span class="page-card-note">
            <i class="fas fa-clock"></i>
            Only approved extra classes count for teacher salary
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-ExtraClasses">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Teacher</th>
                    <th>Batch</th>
                    <th>Time</th>
                    <th>Salary Minutes</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($extraClasses as $extraClass)
                    <tr>
                        <td>
                            <p class="table-main-text">
                                {{ $extraClass->class_date ? \Carbon\Carbon::parse($extraClass->class_date)->format('d M Y') : '-' }}
                            </p>
                            <p class="table-sub-text">Class Date</p>
                        </td>

                        <td>
                            <div class="inline-flex-center">
                                @php
                                    $teacherName = $extraClass->teacher->user->name ?? 'Teacher';
                                    $colors = ['#4F46E5','#0EA5E9','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#14B8A6'];
                                    $color = $colors[$loop->index % count($colors)];
                                @endphp

                                <div class="avatar-circle" style="background: {{ $color }};">
                                    {{ strtoupper(substr($teacherName, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="table-main-text">{{ $teacherName }}</p>
                                    <p class="table-sub-text">Faculty</p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <p class="table-main-text">{{ $extraClass->batch->name ?? '-' }}</p>
                            <p class="table-sub-text">Batch</p>
                        </td>

                        <td>
                            <span class="code-pill">
                                {{ $extraClass->start_time ?? '-' }} - {{ $extraClass->end_time ?? '-' }}
                            </span>
                        </td>

                        <td>
                            @if($extraClass->approval_status === 'approved')
                                <span class="status-pill" style="background:#EDE9FE;color:#6D28D9;">
                                    {{ $extraClass->salary_minutes ?? 0 }} min
                                </span>
                            @else
                                <span class="status-pill warning">
                                    0 min
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($extraClass->approval_status === 'approved')
                                <strong style="color:#047857;">
                                    ₹{{ number_format($extraClass->salary_amount, 2) }}
                                </strong>
                            @else
                                <strong style="color:#92400E;">
                                    ₹0.00
                                </strong>
                            @endif
                        </td>

                        <td>
                            @if($extraClass->approval_status === 'approved')
                                <span class="status-pill success">Approved</span>
                            @elseif($extraClass->approval_status === 'rejected')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Rejected</span>
                            @elseif($extraClass->approval_status === 'pending')
                                <span class="status-pill warning">Pending</span>
                            @else
                                <span class="status-pill" style="background:#F1F5F9;color:#475569;">
                                    {{ ucfirst($extraClass->approval_status ?? '-') }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('extra_class_show')
                                    <a class="btn-outline" href="{{ route('admin.extra-classes.show', $extraClass->id) }}">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                @endcan

                                @can('extra_class_edit')
                                    <a class="btn-outline btn-outline-edit" href="{{ route('admin.extra-classes.edit', $extraClass->id) }}">
                                        <i class="fas fa-pencil-alt"></i>
                                        Edit
                                    </a>
                                @endcan

                                @can('extra_class_approve')
                                    @if($extraClass->approval_status !== 'approved')
                                        <form method="POST"
                                              action="{{ route('admin.extra-classes.approve', $extraClass->id) }}"
                                              style="display:inline;">
                                            @csrf

                                            <button type="submit" class="btn-outline">
                                                <i class="fas fa-check"></i>
                                                Approve
                                            </button>
                                        </form>
                                    @else
                                        <span class="status-pill success">
                                            <i class="fas fa-check"></i>
                                            Done
                                        </span>
                                    @endif

                                    @if($extraClass->approval_status !== 'rejected')
                                        <form method="POST"
                                              action="{{ route('admin.extra-classes.reject', $extraClass->id) }}"
                                              style="display:inline;"
                                              onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                            @csrf

                                            <button type="submit" class="btn-outline btn-outline-danger">
                                                <i class="fas fa-times"></i>
                                                Reject
                                            </button>
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
    initAdminDataTable('.datatable-ExtraClasses', {
        searchPlaceholder: 'Search extra classes...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ extra classes'
    });
});
</script>
@endsection