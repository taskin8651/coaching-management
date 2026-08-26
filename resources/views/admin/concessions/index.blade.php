@extends('layouts.admin')

@section('page-title', 'Concessions')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Concessions & Scholarships</h2>
        <p class="admin-page-subtitle">
            Student-specific concessions that adjust the fee ledger without changing the master fee structure
        </p>
    </div>

    @can('concession_create')
        <a href="{{ route('admin.concessions.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Concession
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total</p>
        <p class="stat-value">{{ $concessions->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Pending Approval</p>
        <p class="stat-value">{{ $concessions->where('approval_status', 'pending')->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Approved</p>
        <p class="stat-value">{{ $concessions->where('approval_status', 'approved')->count() }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Rejected</p>
        <p class="stat-value">{{ $concessions->where('approval_status', 'rejected')->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Concessions</p>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-Concession">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Approval</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($concessions as $concession)
                    <tr data-entry-id="{{ $concession->id }}">
                        <td></td>
                        <td><span class="id-text">#{{ $loop->iteration }}</span></td>
                        <td>
                            <p class="table-main-text">{{ $concession->student->user->name ?? '-' }}</p>
                            <p class="table-sub-text">{{ $concession->ledger->feeStructure->title ?? '-' }}</p>
                        </td>
                        <td>{{ $concession->type }}</td>
                        <td>{{ $concession->amount_type == 'percentage' ? number_format($concession->percentage, 1) . '%' : '₹' . number_format($concession->amount, 2) }}</td>
                        <td>
                            @if($concession->approval_status == 'approved')
                                <span class="status-pill success">Approved</span>
                            @elseif($concession->approval_status == 'rejected')
                                <span class="status-pill" style="background:#FEE2E2;color:#991B1B;">Rejected</span>
                            @else
                                <span class="status-pill warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-row">
                                @can('concession_show')
                                    <a href="{{ route('admin.concessions.show', $concession->id) }}" class="btn-outline"><i class="fas fa-eye"></i> View</a>
                                @endcan

                                @can('concession_approve')
                                    @if($concession->approval_status == 'pending')
                                        <form action="{{ route('admin.concessions.approve', $concession->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn-outline" style="color:#166534;"><i class="fas fa-check"></i> Approve</button>
                                        </form>
                                        <form action="{{ route('admin.concessions.reject', $concession->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn-outline btn-outline-danger"><i class="fas fa-times"></i> Reject</button>
                                        </form>
                                    @endif
                                @endcan

                                @can('concession_edit')
                                    @if($concession->approval_status == 'pending')
                                        <a href="{{ route('admin.concessions.edit', $concession->id) }}" class="btn-outline btn-outline-edit"><i class="fas fa-pencil-alt"></i> Edit</a>
                                    @endif
                                @endcan

                                @can('concession_delete')
                                    @if($concession->approval_status == 'pending')
                                        <form action="{{ route('admin.concessions.destroy', $concession->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
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
    initAdminDataTable('.datatable-Concession', {
        canDelete: false,
        searchPlaceholder: 'Search concessions...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ concessions'
    });
});
</script>
@endsection
