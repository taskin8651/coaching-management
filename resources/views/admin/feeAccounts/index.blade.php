@extends('layouts.admin')

@section('page-title', 'Fee Accounts')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Fee Accounts</h2>
        <p class="admin-page-subtitle">
            Collection accounts (bank / cash / UPI) used to receive fee payments
        </p>
    </div>

    @can('fee_account_create')
        <a href="{{ route('admin.fee-accounts.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Fee Account
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Accounts</p>
        <p class="stat-value">{{ $feeAccounts->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $feeAccounts->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Bank Accounts</p>
        <p class="stat-value">{{ $feeAccounts->where('type', 'bank')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">GST Registered</p>
        <p class="stat-value">{{ $feeAccounts->where('gst_applicable', true)->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Fee Accounts</p>
        <span class="page-card-note"><i class="fas fa-info-circle"></i> Used on installments, payments and receipts</span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-FeeAccount">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Account</th>
                    <th>Type</th>
                    <th>Branch</th>
                    <th>GST</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($feeAccounts as $feeAccount)
                    <tr data-entry-id="{{ $feeAccount->id }}">
                        <td></td>
                        <td><span class="id-text">#{{ $loop->iteration }}</span></td>

                        <td>
                            <p class="table-main-text">{{ $feeAccount->name }}</p>
                            <p class="table-sub-text">{{ $feeAccount->code }}{{ $feeAccount->type == 'bank' ? ' • ' . $feeAccount->bank_name : '' }}</p>
                        </td>

                        <td>{{ ucfirst($feeAccount->type) }}</td>
                        <td>{{ $feeAccount->branch->name ?? 'Shared / All Branches' }}</td>

                        <td>
                            @if($feeAccount->gst_applicable)
                                <span class="status-pill success">{{ $feeAccount->gst_number ?? 'GST' }}</span>
                            @else
                                <span class="status-pill">No GST</span>
                            @endif
                        </td>

                        <td>
                            @if($feeAccount->status == 'active')
                                <span class="status-pill success">Active</span>
                            @else
                                <span class="status-pill warning">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('fee_account_show')
                                    <a href="{{ route('admin.fee-accounts.show', $feeAccount->id) }}" class="btn-outline"><i class="fas fa-eye"></i> View</a>
                                @endcan
                                @can('fee_account_edit')
                                    <a href="{{ route('admin.fee-accounts.edit', $feeAccount->id) }}" class="btn-outline btn-outline-edit"><i class="fas fa-pencil-alt"></i> Edit</a>
                                @endcan
                                @can('fee_account_delete')
                                    <form action="{{ route('admin.fee-accounts.destroy', $feeAccount->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn-outline btn-outline-danger"><i class="fas fa-trash-alt"></i> Delete</button>
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
    initAdminDataTable('.datatable-FeeAccount', {
        canDelete: @can('fee_account_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.fee-accounts.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search fee accounts...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ fee accounts'
    });
});
</script>
@endsection
