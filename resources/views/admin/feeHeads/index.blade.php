@extends('layouts.admin')

@section('page-title', 'Fee Heads')

@section('content')

<div class="admin-page-head">
    <div>
        <h2 class="admin-page-title">Fee Heads (Fee Master)</h2>
        <p class="admin-page-subtitle">
            Configurable fee heads used across fee structures — no fee head is hard-coded
        </p>
    </div>

    @can('fee_master_create')
        <a href="{{ route('admin.fee-heads.create') }}" class="btn-primary">
            <i class="fas fa-plus"></i>
            Add Fee Head
        </a>
    @endcan
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Fee Heads</p>
        <p class="stat-value">{{ $feeHeads->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">Active</p>
        <p class="stat-value">{{ $feeHeads->where('status', 'active')->count() }}</p>
    </div>

    <div class="stat-card">
        <p class="stat-label">GST Applicable</p>
        <p class="stat-value">{{ $feeHeads->where('gst_applicable', true)->count() }}</p>
    </div>
</div>

<div class="page-card">
    <div class="page-card-header">
        <p class="page-card-title">All Fee Heads</p>

        <span class="page-card-note">
            <i class="fas fa-info-circle"></i>
            Used as line items when building a fee structure
        </span>
    </div>

    <div class="page-card-table">
        <table class="min-w-full datatable datatable-FeeHead">
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Fee Head</th>
                    <th>GST</th>
                    <th>Status</th>
                    <th style="text-align:right;">{{ trans('global.actions') }}</th>
                </tr>
            </thead>

            <tbody>
                @foreach($feeHeads as $feeHead)
                    <tr data-entry-id="{{ $feeHead->id }}">
                        <td></td>

                        <td><span class="id-text">#{{ $loop->iteration }}</span></td>

                        <td><span class="code-pill">{{ $feeHead->code }}</span></td>

                        <td>
                            <p class="table-main-text">{{ $feeHead->name }}</p>
                            <p class="table-sub-text">{{ $feeHead->description ? \Illuminate\Support\Str::limit($feeHead->description, 35) : '-' }}</p>
                        </td>

                        <td>
                            @if($feeHead->gst_applicable)
                                <span class="status-pill success">{{ number_format($feeHead->default_gst_percent, 1) }}%</span>
                            @else
                                <span class="status-pill">No GST</span>
                            @endif
                        </td>

                        <td>
                            @if($feeHead->status == 'active')
                                <span class="status-pill success">Active</span>
                            @else
                                <span class="status-pill warning">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-row">
                                @can('fee_master_show')
                                    <a href="{{ route('admin.fee-heads.show', $feeHead->id) }}" class="btn-outline">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                @endcan

                                @can('fee_master_edit')
                                    <a href="{{ route('admin.fee-heads.edit', $feeHead->id) }}" class="btn-outline btn-outline-edit">
                                        <i class="fas fa-pencil-alt"></i> Edit
                                    </a>
                                @endcan

                                @can('fee_master_delete')
                                    <form action="{{ route('admin.fee-heads.destroy', $feeHead->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ trans('global.areYouSure') }}')">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn-outline btn-outline-danger">
                                            <i class="fas fa-trash-alt"></i> Delete
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
    initAdminDataTable('.datatable-FeeHead', {
        canDelete: @can('fee_master_delete') true @else false @endcan,
        massDeleteUrl: "{{ route('admin.fee-heads.massDestroy') }}",
        deleteText: "{{ trans('global.datatables.delete') }}",
        zeroSelectedText: "{{ trans('global.datatables.zero_selected') }}",
        confirmText: "{{ trans('global.areYouSure') }}",
        searchPlaceholder: 'Search fee heads...',
        infoText: 'Showing _START_–_END_ of _TOTAL_ fee heads'
    });
});
</script>
@endsection
